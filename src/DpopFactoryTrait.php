<?php

namespace Pdsinterop\Solid;

use DateInterval;
// use OCP\IDBConnection;
use Pdsinterop\Solid\Auth\Utils\DPop;
use Pdsinterop\Solid\Auth\Utils\JtiValidator;

trait DpopFactoryTrait
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    // private IDBConnection $connection;
    private DateInterval $validFor;

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    final public function getDpop(): DPop
    {
        $interval = $this->getDpopValidFor();

        // $replayDetector = new JtiReplayDetector($interval, $this->connection);
        $replayDetector = new JtiReplayDetector($interval, null);

        $jtiValidator = new JtiValidator($replayDetector);

        return new DPop($jtiValidator);
    }

    final public function getDpopValidFor(): DateInterval
    {
        static $validFor;

        if ($validFor === null) {
            $validFor = new DateInterval('PT10M');
        }

        return $validFor;
    }

    final public function setJtiStorage(): void
    {
        // FIXME
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}
