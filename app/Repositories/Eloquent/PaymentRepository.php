<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    /**
     * PaymentRepository constructor.
     *
     * @param Payment $model
     */
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with(['course'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByCourseId(int $courseId): Collection
    {
        return $this->model->where('course_id', $courseId)
            ->with(['user'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['user', 'course'])
            ->get();
    }

/**
     * @inheritDoc
     */
    public function findByPaymentId(string $paymentId): ?Payment
    {
        return $this->model->where('payment_id', $paymentId)
            ->with(['user', 'course'])
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function updateStatus(string $paymentId, string $status): ?Payment
    {
        $payment = $this->findByPaymentId($paymentId);
        
        if (!$payment) {
            return null;
        }
        
        $payment->update(['status' => $status]);
        
        return $payment->fresh();
    }
}