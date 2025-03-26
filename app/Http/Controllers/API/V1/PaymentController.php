<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PaymentController extends Controller
{
    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var CourseRepositoryInterface
     */
    private $courseRepository;

    /**
     * @var StudentRepositoryInterface
     */
    private $studentRepository;

    /**
     * @var EnrollmentRepositoryInterface
     */
    private $enrollmentRepository;

    /**
     * PaymentController constructor.
     *
     * @param PaymentRepositoryInterface $paymentRepository
     * @param CourseRepositoryInterface $courseRepository
     * @param StudentRepositoryInterface $studentRepository
     * @param EnrollmentRepositoryInterface $enrollmentRepository
     */
    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        CourseRepositoryInterface $courseRepository,
        StudentRepositoryInterface $studentRepository,
        EnrollmentRepositoryInterface $enrollmentRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->courseRepository = $courseRepository;
        $this->studentRepository = $studentRepository;
        $this->enrollmentRepository = $enrollmentRepository;

        // Set Stripe API key
        Stripe::setApiKey(config('cashier.secret'));
    }

    /**
     * Display a listing of the payments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->isAdmin()) {
            $payments = $this->paymentRepository->all(['*'], ['user', 'course']);
        } else {
            $payments = $this->paymentRepository->getByUserId($user->id);
        }
        
        return response()->json(['payments' => $payments]);
    }

    /**
     * Create a Stripe checkout session for a course.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        
        if (!$user->isStudent()) {
            return response()->json([
                'message' => 'Only students can purchase courses',
            ], 403);
        }
        
        $student = $this->studentRepository->findByUserId($user->id);
        
        if (!$student) {
            return response()->json([
                'message' => 'You need to create a student profile first',
            ], 403);
        }
        
        $course = $this->courseRepository->findById($request->course_id);
        
        if ($course->is_free) {
            return response()->json([
                'message' => 'This course is free, no payment required',
            ], 400);
        }
        
        if ($this->enrollmentRepository->isEnrolled($student->id, $course->id)) {
            return response()->json([
                'message' => 'You are already enrolled in this course',
            ], 409);
        }
        
        // Create a Stripe checkout session
        $price = $course->discount_price ?? $course->price;
        
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $course->title,
                                'description' => $course->description,
                            ],
                            'unit_amount' => $price * 100, // Convert to cents
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => url('/api/V1/payments/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/api/V1/payments/cancel?session_id={CHECKOUT_SESSION_ID}'),
                'metadata' => [
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ],
            ]);
            
            // Create a payment record
            $this->paymentRepository->create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'payment_id' => $session->id,
                'amount' => $price,
                'currency' => 'usd',
                'status' => 'pending',
            ]);
            
            return response()->json([
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create checkout session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle successful payment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function success(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment = $this->paymentRepository->findByPaymentId($request->session_id);
        
        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found',
            ], 404);
        }
        
        // Update payment status
        $this->paymentRepository->updateStatus($request->session_id, 'completed');
        
        // Get session details from Stripe
        try {
            $session = Session::retrieve($request->session_id);
            
            if ($session->payment_status === 'paid') {
                // Update payment record
                $payment->update([
                    'payment_method' => 'card',
                    'receipt_url' => $session->receipt_url ?? null,
                ]);
                
                // Create enrollment
                $student = $this->studentRepository->findByUserId($payment->user_id);
                
                if ($student) {
                    $this->enrollmentRepository->create([
                        'course_id' => $payment->course_id,
                        'student_id' => $student->id,
                        'status' => 'approved',
                        'progress' => 0,
                    ]);
                }
            }
            
            return response()->json([
                'message' => 'Payment successful',
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve session details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle cancelled payment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update payment status
        $this->paymentRepository->updateStatus($request->session_id, 'failed');
        
        return response()->json([
            'message' => 'Payment cancelled',
        ]);
    }

    /**
     * Display the specified payment.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $payment = $this->paymentRepository->findById($id, ['*'], ['user', 'course']);
        
        // Only admin or the payment owner can view payment details
        if (!$user->isAdmin() && $payment->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to view this payment',
            ], 403);
        }
        
        return response()->json(['payment' => $payment]);
    }

    /**
     * Get user's payment history.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $payments = $this->paymentRepository->getByUserId($user->id);
        
        return response()->json(['payments' => $payments]);
    }

    /**
     * Get payments by course ID.
     *
     * @param Request $request
     * @param int $courseId
     * @return JsonResponse
     */
    public function getByCourseId(Request $request, int $courseId): JsonResponse
    {
        $user = $request->user();
        $course = $this->courseRepository->findById($courseId, ['*'], ['mentor']);
        
        // Only admin or the course owner can view payments for a course
        if (!$user->isAdmin() && $course->mentor->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to view payments for this course',
            ], 403);
        }
        
        $payments = $this->paymentRepository->getByCourseId($courseId);
        
        return response()->json(['payments' => $payments]);
    }

    /**
     * Get payments by status.
     *
     * @param Request $request
     * @param string $status
     * @return JsonResponse
     */
    public function getByStatus(Request $request, string $status): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to view all payments by status',
            ], 403);
        }
        
        if (!in_array($status, ['pending', 'completed', 'failed', 'refunded'])) {
            return response()->json([
                'message' => 'Invalid payment status',
            ], 422);
        }
        
        $payments = $this->paymentRepository->getByStatus($status);
        
        return response()->json(['payments' => $payments]);
    }
}