<?php

namespace Tests\Feature;

use Tests\TestCase;

class RemovedInternalEndpointsTest extends TestCase
{
    /**
     * @dataProvider removedEndpointProvider
     */
    public function test_removed_endpoints_return_not_found_to_guests(string $path): void
    {
        $this->get($path)->assertNotFound();
    }

    public function removedEndpointProvider(): array
    {
        return [
            'annealing debug data' => ['/annealing-checks/debug'],
            'material AI export' => ['/material-monitoring-checksheets/for-ai'],
            'welding item-code rules' => ['/welding-checksheets/item-code-rules'],
        ];
    }
}
