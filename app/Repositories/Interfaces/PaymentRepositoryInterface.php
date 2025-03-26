<?php

namespace App\Repositories\Interfaces;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface extends EloquentRepositoryInterface
{
    /**
     * Get payments by user ID.
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get payments by course ID.
     *
     * @param int $courseId
     * @return Collection
     */
    public function getByCourseId(int $courseId): Collection;

    /**
     * Get payments by status.
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Find payment by payment ID.
     *
     * @param string $paymentId
     * @return Payment|null
     */
    public function findByPaymentId(string $paymentId): ?Payment;

    /**
     * Update payment status.
     *
     * @param string $paymentId
     * @param string $status
     * @return Payment|null
     */
    public function updateStatus(string $paymentId, string $status): ?Payment;
}