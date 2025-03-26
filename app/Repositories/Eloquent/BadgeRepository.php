<?php

namespace App\Repositories\Eloquent;

use App\Models\Badge;
use App\Repositories\Interfaces\BadgeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BadgeRepository extends BaseRepository implements BadgeRepositoryInterface
{
    /**
     * BadgeRepository constructor.
     *
     * @param Badge $model
     */
    public function __construct(Badge $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByType(string $type): Collection
    {
        return $this->model->where('type', $type)->get();
    }

    /**
     * @inheritDoc
     */
    public function awardToStudent(int $badgeId, int $studentId): bool
    {
        $badge = $this->findById($badgeId);
        
        if ($badge->type !== 'student') {
            return false;
        }
        
        if ($this->studentHasBadge($badgeId, $studentId)) {
            return true;
        }
        
        $badge->students()->attach($studentId, ['earned_at' => now()]);
        
        return true;
    }

    /**
     * @inheritDoc
     */
    public function awardToMentor(int $badgeId, int $mentorId): bool
    {
        $badge = $this->findById($badgeId);
        
        if ($badge->type !== 'mentor') {
            return false;
        }
        
        if ($this->mentorHasBadge($badgeId, $mentorId)) {
            return true;
        }
        
        $badge->mentors()->attach($mentorId, ['earned_at' => now()]);
        
        return true;
    }

    /**
     * @inheritDoc
     */
    public function removeFromStudent(int $badgeId, int $studentId): bool
    {
        $badge = $this->findById($badgeId);
        $badge->students()->detach($studentId);
        
        return true;
    }

    /**
     * @inheritDoc
     */
    public function removeFromMentor(int $badgeId, int $mentorId): bool
    {
        $badge = $this->findById($badgeId);
        $badge->mentors()->detach($mentorId);
        
        return true;
    }

    /**
     * @inheritDoc
     */
    public function studentHasBadge(int $badgeId, int $studentId): bool
    {
        $badge = $this->findById($badgeId);
        
        return $badge->students()->where('student_id', $studentId)->exists();
    }

    /**
     * @inheritDoc
     */
    public function mentorHasBadge(int $badgeId, int $mentorId): bool
    {
        $badge = $this->findById($badgeId);
        
        return $badge->mentors()->where('mentor_id', $mentorId)->exists();
    }
}