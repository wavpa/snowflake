<?php

namespace Wavpa\Snowflake;

use Wavpa\Snowflake\Exceptions\Exception;

/**
 * Snowflake ID Generator
 */
class Snowflake
{
    const EPOCH               = 1500000000000;
    const WORKER_ID_BITS      = 5;
    const DATA_CENTER_ID_BITS = 5;
    const SEQUENCE_BITS       = 12;

    protected $workerId;
    protected $dataCenterId;
    protected $sequence = 0;

    protected $maxWorkerId     = -1 ^ (-1 << self::WORKER_ID_BITS);
    protected $maxDataCenterId = -1 ^ (-1 << self::DATA_CENTER_ID_BITS);

    protected $workerIdShift      = self::SEQUENCE_BITS;
    protected $datacenterIdShift  = self::SEQUENCE_BITS + self::WORKER_ID_BITS;
    protected $timestampLeftShift = self::SEQUENCE_BITS + self::WORKER_ID_BITS + self::DATA_CENTER_ID_BITS;
    protected $sequenceMask       = -1 ^ (-1 << self::SEQUENCE_BITS);

    protected $lastTimestamp = -1;

    public function __construct($workerId = 0, $dataCenterId = 0)
    {
        $this->workerId = $workerId > $this->maxWorkerId || $workerId < 0 ? mt_rand(0, 31) : $workerId;

        $this->dataCenterId = $dataCenterId > $this->maxDataCenterId || $dataCenterId < 0 ? mt_rand(0, 31) : $dataCenterId;
    }

    public function nextId()
    {
        $timestamp = $this->getCurrentMicrotime();

        if ($timestamp < $this->lastTimestamp) {
            $diffTimestamp = bcsub($this->lastTimestamp, $timestamp);
            throw new Exception("Clock moved backwards. Refusing to generate id for {$diffTimestamp} milliseconds");
        }

        if ($this->lastTimestamp == $timestamp) {
            $this->sequence = ($this->sequence + 1) & $this->sequenceMask;

            if (0 == $this->sequence) {
                $timestamp = $this->tilNextMillis($this->lastTimestamp);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;

        $gmpTimestamp    = gmp_init($this->leftShift(bcsub($timestamp, self::EPOCH), $this->timestampLeftShift));
        $gmpDatacenterId = gmp_init($this->leftShift($this->dataCenterId, $this->datacenterIdShift));
        $gmpWorkerId     = gmp_init($this->leftShift($this->workerId, $this->workerIdShift));
        $gmpSequence     = gmp_init($this->sequence);

        return gmp_strval(gmp_or(gmp_or(gmp_or($gmpTimestamp, $gmpDatacenterId), $gmpWorkerId), $gmpSequence));
    }

    protected function tilNextMillis($lastTimestamp)
    {
        $timestamp = $this->getCurrentMicrotime();

        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->getCurrentMicrotime();
        }

        return $timestamp;
    }

    protected function getCurrentMicrotime()
    {
        return floor(microtime(true) * 1000);
    }

    protected function leftShift($a, $b)
    {
        return bcmul($a, bcpow(2, $b));
    }
}
