<?php

namespace App\Repositories\Eloquent;

use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EnrollmentRepository extends BaseRepository implements EnrollmentRepositoryInterface
{
    /**
     * EnrollmentRepository constructor.
     *
     * @param Enrollment $model
     */
    public function __construct(Enrollment $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByCourseId(int $courseId): Collection
    {
        return $this->model->where('course_id', $courseId)
            ->with(['student.user'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByStudentId(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)
            ->with(['course.mentor.user'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->with(['course.mentor.user', 'student.user'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function isEnrolled(int $studentId, int $courseId): bool
    {
        return $this->model->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->exists();
    }

    /**
     * @inheritDoc
     */
    public function updateProgress(int $enrollmentId, int $progress, ?int $lastWatchedVideoId = null): Enrollment
    {
        $enrollment = $this->findById($enrollmentId);
        
        $data = ['progress' => $progress];
        
        if ($lastWatchedVideoId) {
            $data['last_watched_video_id'] = $lastWatchedVideoId;
        }
        
        if ($progress === 100 && !$enrollment->completed_at) {
            $data['completed_at'] = now();
        }
        
        $enrollment->update($data);
        
        return $enrollment->fresh();
    }

    /**
     * @inheritDoc
     */
    public function complete(int $enrollmentId): Enrollment
    {
        $enrollment = $this->findById($enrollmentId);
        
        $enrollment->update([
            'progress' => 100,
            'completed_at' => now(),
        ]);
        
        return $enrollment->fresh();
    }
}