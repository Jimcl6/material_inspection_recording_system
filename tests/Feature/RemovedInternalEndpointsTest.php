<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RemovedInternalEndpointsTest extends TestCase
{
    #[DataProvider('removedEndpointProvider')]
    public function test_removed_endpoints_return_not_found_to_guests(string $path): void
    {
        $this->get($path)->assertNotFound();
    }

    public static function removedEndpointProvider(): array
    {
        return [
            'annealing debug data' => ['/annealing-checks/debug'],
            'material AI export' => ['/material-monitoring-checksheets/for-ai'],
            'welding item-code rules' => ['/welding-checksheets/item-code-rules'],
        ];
    }
}
