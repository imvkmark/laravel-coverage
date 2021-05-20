<?php

namespace Poppy\System\Tests\Ability;

use Carbon\Carbon;
use Poppy\System\Events\PamDisableEvent;
use Poppy\System\Tests\Base\SystemTestCase;

class EventTest extends SystemTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->initPam();
    }

    public function testPamDisable(): void
    {
        event(new PamDisableEvent($this->pam, $this->pam, 'Testing Event dispatched @ ' . Carbon::now()));
        $this->assertTrue(true);
    }
}
