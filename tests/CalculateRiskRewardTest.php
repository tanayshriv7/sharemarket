<?php
require_once __DIR__ . '/../PatternAnalysisService.php';

use PHPUnit\Framework\TestCase;
use App\Services\PatternAnalysisService;
use ReflectionMethod;

class CalculateRiskRewardTest extends TestCase
{
    private function invokeCalculateRiskReward($return, string $riskLevel): float
    {
        $service = new PatternAnalysisService();
        $method = new ReflectionMethod(PatternAnalysisService::class, 'calculateRiskReward');
        $method->setAccessible(true);
        return $method->invoke($service, $return, $riskLevel);
    }

    public function riskRewardProvider(): array
    {
        return [
            'low risk' => ['10%', 'low', 1.5],
            'medium risk' => ['10%', 'medium', 1.2],
            'high risk' => ['10%', 'high', 0.8],
            'unknown risk' => ['10%', 'unknown', 1.0],
        ];
    }

    /**
     * @dataProvider riskRewardProvider
     */
    public function testCalculateRiskReward($return, string $riskLevel, float $expected): void
    {
        $result = $this->invokeCalculateRiskReward($return, $riskLevel);
        $this->assertIsFloat($result);
        $this->assertSame($expected, $result);
    }
}
