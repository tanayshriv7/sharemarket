<?php



namespace App\Services;



use App\Models\Stock;

use App\Models\Pattern;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Collection;

use Carbon\Carbon;

use Exception;

use GuzzleHttp\Client;



class PatternAnalysisService

{

    // Pattern types configuration (as provided in your code)

    private array $patternTypes = [

        'Ascending Triangle Chart Pattern' => [

            'reliability' => 85,

            'avg_return' => 15,

            'risk_level' => 'medium',

            'category' => 'continuation',

            'bullish_probability' => 78

        ],

        'Descending Triangle Chart Pattern' => [

            'reliability' => 82,

            'avg_return' => 12,

            'risk_level' => 'medium',

            'category' => 'continuation',

            'bullish_probability' => 25

        ],

        'Symmetrical Triangle Chart Pattern' => [

            'reliability' => 80,

            'avg_return' => 14,

            'risk_level' => 'medium',

            'category' => 'bilateral',

            'bullish_probability' => 50

        ],

        'Pennant Chart Pattern' => [

            'reliability' => 83,

            'avg_return' => 16,

            'risk_level' => 'medium',

            'category' => 'continuation',

            'bullish_probability' => 70

        ],

        'Bullish Flag Chart Pattern' => [

            'reliability' => 84,

            'avg_return' => 15,

            'risk_level' => 'low',

            'category' => 'continuation',

            'bullish_probability' => 75

        ],

        'Bearish Flag Chart Pattern' => [

            'reliability' => 84,

            'avg_return' => 15,

            'risk_level' => 'low',

            'category' => 'continuation',

            'bullish_probability' => 25

        ],

        'Rising Wedge Chart Pattern' => [

            'reliability' => 81,

            'avg_return' => 13,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 30

        ],

        'Falling Wedge Chart Pattern' => [

            'reliability' => 81,

            'avg_return' => 13,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 70

        ],

        'Double Bottom Chart Pattern' => [

            'reliability' => 88,

            'avg_return' => 18,

            'risk_level' => 'low',

            'category' => 'reversal',

            'bullish_probability' => 85

        ],

        'Double Top Chart Pattern' => [

            'reliability' => 86,

            'avg_return' => 16,

            'risk_level' => 'low',

            'category' => 'reversal',

            'bullish_probability' => 15

        ],

        'Head and Shoulders Chart Pattern' => [

            'reliability' => 90,

            'avg_return' => 20,

            'risk_level' => 'low',

            'category' => 'reversal',

            'bullish_probability' => 10

        ],

        'Inverse Head and Shoulders Pattern' => [

            'reliability' => 89,

            'avg_return' => 19,

            'risk_level' => 'low',

            'category' => 'reversal',

            'bullish_probability' => 90

        ],

        'Rounding Top Chart Pattern' => [

            'reliability' => 87,

            'avg_return' => 17,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 20

        ],

        'Rounding Bottom Chart Pattern' => [

            'reliability' => 87,

            'avg_return' => 17,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 80

        ],

        'Cup and Handle Chart Pattern' => [

            'reliability' => 92,

            'avg_return' => 22,

            'risk_level' => 'low',

            'category' => 'continuation',

            'bullish_probability' => 88

        ],

        'Bump and Run Chart Patterns' => [

            'reliability' => 85,

            'avg_return' => 20,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 75

        ],

        'Price Channel Chart Pattern' => [

            'reliability' => 80,

            'avg_return' => 12,

            'risk_level' => 'low',

            'category' => 'continuation',

            'bullish_probability' => 50

        ],

        'Triple Top Chart Patterns' => [

            'reliability' => 88,

            'avg_return' => 18,

            'risk_level' => 'low',

            'category' => 'reversal',

            'bullish_probability' => 15

        ],

        'Triple Bottom Chart Pattern' => [

            'reliability' => 88,

            'avg_return' => 18,

            'risk_level' => 'low',

            'category' => 'reversal',

            'bullish_probability' => 85

        ],

        'Diamond Top Chart Pattern' => [

            'reliability' => 86,

            'avg_return' => 16,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 20

        ],

        'Diamond Bottom Chart Pattern' => [

            'reliability' => 86,

            'avg_return' => 16,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 80

        ],

        'Channel Chart Patterns' => [

            'reliability' => 80,

            'avg_return' => 12,

            'risk_level' => 'low',

            'category' => 'continuation',

            'bullish_probability' => 50

        ],

        'Gaps Chart Patterns' => [

            'reliability' => 75,

            'avg_return' => 10,

            'risk_level' => 'high',

            'category' => 'volatility',

            'bullish_probability' => 50

        ],

        'Bullish Rectangle Chart Pattern' => [

            'reliability' => 82,

            'avg_return' => 14,

            'risk_level' => 'low',

            'category' => 'continuation',

            'bullish_probability' => 70

        ],

        'Bearish Rectangle Chart Pattern' => [

            'reliability' => 82,

            'avg_return' => 14,

            'risk_level' => 'low',

            'category' => 'continuation',

            'bullish_probability' => 30

        ],

        'Pipe Top Chart Pattern' => [

            'reliability' => 80,

            'avg_return' => 12,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 20

        ],

        'Pipe Bottom Chart Pattern' => [

            'reliability' => 80,

            'avg_return' => 12,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 80

        ],

        'Spikes Stock Chart Pattern' => [

            'reliability' => 70,

            'avg_return' => 8,

            'risk_level' => 'high',

            'category' => 'volatility',

            'bullish_probability' => 50

        ],

        'Ascending Staircase Chart Pattern' => [

            'reliability' => 82,

            'avg_return' => 15,

            'risk_level' => 'low',

            'category' => 'continuation',

            'bullish_probability' => 75

        ],

        'Descending Staircase Chart Pattern' => [

            'reliability' => 82,

            'avg_return' => 15,

            'risk_level' => 'low',

            'category' => 'continuation',

            'bullish_probability' => 25

        ],

        'Megaphone Stock Chart Pattern' => [

            'reliability' => 78,

            'avg_return' => 14,

            'risk_level' => 'high',

            'category' => 'volatility',

            'bullish_probability' => 50

        ],

        'V Chart Pattern' => [

            'reliability' => 80,

            'avg_return' => 15,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 50

        ],

        'Harmonic Chart Pattern' => [

            'reliability' => 85,

            'avg_return' => 18,

            'risk_level' => 'medium',

            'category' => 'complex',

            'bullish_probability' => 50

        ],

        'Elliott Wave Chart Pattern' => [

            'reliability' => 80,

            'avg_return' => 20,

            'risk_level' => 'high',

            'category' => 'complex',

            'bullish_probability' => 50

        ],

        'Three Drives Chart Pattern' => [

            'reliability' => 82,

            'avg_return' => 16,

            'risk_level' => 'medium',

            'category' => 'complex',

            'bullish_probability' => 50

        ],

        'Quasimodo Chart Pattern' => [

            'reliability' => 83,

            'avg_return' => 17,

            'risk_level' => 'medium',

            'category' => 'complex',

            'bullish_probability' => 50

        ],

        'Dead Cat Bounce Chart Pattern' => [

            'reliability' => 78,

            'avg_return' => 10,

            'risk_level' => 'high',

            'category' => 'reversal',

            'bullish_probability' => 20

        ],

        'Island Reversal Chart Pattern' => [

            'reliability' => 80,

            'avg_return' => 12,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 50

        ],

        'Tower Top Chart Pattern' => [

            'reliability' => 82,

            'avg_return' => 14,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 20

        ],

        'Tower Bottom Chart Patterns' => [

            'reliability' => 82,

            'avg_return' => 14,

            'risk_level' => 'medium',

            'category' => 'reversal',

            'bullish_probability' => 80

        ],

        'Shakeout Chart Pattern' => [

            'reliability' => 78,

            'avg_return' => 10,

            'risk_level' => 'high',

            'category' => 'reversal',

            'bullish_probability' => 50

        ],

        'Broadening Wedge Pattern (Expanding Triangle)' => [

            'reliability' => 80,

            'avg_return' => 14,

            'risk_level' => 'high',

            'category' => 'bilateral',

            'bullish_probability' => 50

        ],

        'Parabolic Curve Pattern' => [

            'reliability' => 75,

            'avg_return' => 12,

            'risk_level' => 'high',

            'category' => 'volatility',

            'bullish_probability' => 50

        ],

        'Bullish Wolfe Wave' => [

            'reliability' => 82,

            'avg_return' => 15,

            'risk_level' => 'medium',

            'category' => 'complex',

            'bullish_probability' => 70

        ],

        'Bearish Wolfe Wave Pattern' => [

            'reliability' => 82,

            'avg_return' => 15,

            'risk_level' => 'medium',

            'category' => 'complex',

            'bullish_probability' => 30

        ]

    ];



    private array $timeframes = ['MONTHLY', 'WEEKLY', 'DAILY', 'HOURLY', '15MIN', '5MIN'];



    // New properties for classified patterns

    private array $bullishPatterns = [];

    private array $bearishPatterns = [];

    private array $neutralPatterns = [];



    public function __construct()

    {

        $this->classifyPatterns();

        // Rest of constructor...

    }



    public function getPatternTypes(): array

    {

        return $this->patternTypes;

    }



    /**

     * Update patterns for a stock in database

     */

    public function updateStockPatterns(Stock $stock): array

    {

        try {

            $analysis = $this->analyzeStock($stock);

            $updatedPatterns = [];

            

            // Clear existing patterns for this stock

            Pattern::where('stock_id', $stock->id)->delete();

            

            // Save top 3 patterns per timeframe

            foreach ($analysis['patterns'] as $patternData) {

                $pattern = Pattern::create([

                    'stock_id' => $stock->id,

                    'timeframe' => $patternData['timeframe'],

                    'pattern_name' => $patternData['pattern_name'],

                    'rank' => $this->safeIntegerCast($patternData['rank']),

                    'success_probability' => $this->safeDecimalCast($patternData['success_probability']),

                    'avg_return' => $this->safeDecimalCast($patternData['avg_return']),

                    'reliability' => $this->safeDecimalCast($this->convertReliabilityToNumeric($patternData['reliability'])),

                    'risk_reward' => $this->safeDecimalCast($patternData['risk_reward']),

                    'chart_type' => $patternData['pattern_category'],

                    'detected_at' => $patternData['detected_at']

                ]);

                

                $updatedPatterns[] = $pattern;

            }

            

            return [

                'success' => true,

                'patterns_created' => count($updatedPatterns),

                'patterns' => $updatedPatterns

            ];

            

        } catch (Exception $e) {

            Log::error("Pattern update failed for stock {$stock->symbol}: " . $e->getMessage());

            

            return [

                'success' => false,

                'error' => $e->getMessage(),

                'patterns_created' => 0

            ];

        }

    }



    /**

     * Safe decimal casting with validation

     */

    private function safeDecimalCast($value): float

    {

        if ($value === null || $value === '') {

            return 0.0;

        }

        

        if (is_string($value)) {

            // Remove any non-numeric characters except decimal point and minus sign

            $cleanValue = preg_replace('/[^\d.-]/', '', $value);

            

            if ($cleanValue === '' || $cleanValue === '-') {

                return 0.0;

            }

            

            $value = $cleanValue;

        }

        

        if (is_numeric($value)) {

            return (float) $value;

        }

        

        Log::warning("Unable to cast value to decimal: " . var_export($value, true));

        return 0.0;

    }



    /**

     * Safe integer casting with validation

     */

    private function safeIntegerCast($value): int

    {

        if ($value === null || $value === '') {

            return 0;

        }

        

        if (is_string($value)) {

            // Remove any non-numeric characters except minus sign

            $cleanValue = preg_replace('/[^\d-]/', '', $value);

            

            if ($cleanValue === '' || $cleanValue === '-') {

                return 0;

            }

            

            $value = $cleanValue;

        }

        

        if (is_numeric($value)) {

            return (int) $value;

        }

        

        Log::warning("Unable to cast value to integer: " . var_export($value, true));

        return 0;

    }



    /**

     * Enhanced stock analysis with caching

     */

    public function analyzeStock(Stock $stock): array

    {

        $cacheKey = "stock_analysis_{$stock->id}_" . ($stock->updated_at ? $stock->updated_at->timestamp : time());

        

        return Cache::remember($cacheKey, 300, function() use ($stock) {

            $patternsByTimeframe = [];

            $totalScore = 0;

            $patternCount = 0;

            $bullishPatterns = 0;

            $bearishPatterns = 0;



            foreach ($this->timeframes as $timeframe) {

                $patterns = $this->detectPattern($stock, $timeframe); // Now returns array of patterns

                if (!empty($patterns)) {

                    $patternsByTimeframe[$timeframe] = $patterns;

                    foreach ($patterns as $pattern) {

                        $totalScore += $this->safeDecimalCast($pattern['success_probability']);

                        $patternCount++;

                        

                        $bullishProb = $this->safeDecimalCast($pattern['bullish_probability']);

                        if ($bullishProb > 60) {

                            $bullishPatterns++;

                        } elseif ($bullishProb < 40) {

                            $bearishPatterns++;

                        }

                    }

                }

            }



            // Select top 3 patterns per timeframe based on rank, with tie-breaking by success_probability

            $filteredPatterns = [];

            foreach ($patternsByTimeframe as $timeframe => $patterns) {

                // Sort by rank (asc) then success_probability (desc)

                usort($patterns, function($a, $b) {

                    if ($a['rank'] === $b['rank']) {

                        return $b['success_probability'] <=> $a['success_probability'];

                    }

                    return $a['rank'] <=> $b['rank'];

                });

                $filteredPatterns = array_merge($filteredPatterns, array_slice($patterns, 0, 3));

            }



            $multiTfScore = $patternCount > 0 ? round($totalScore / $patternCount, 2) : 0;

            $recommendation = $this->getRecommendation($multiTfScore, $patternCount, $bullishPatterns, $bearishPatterns);

            $sureshotAnalysis = $this->detectSureshot($stock, $filteredPatterns);



            return [

                'patterns' => $filteredPatterns,

                'multi_tf_score' => $this->safeDecimalCast($multiTfScore),

                'recommendation' => $recommendation,

                'pattern_count' => count($filteredPatterns),

                'bullish_patterns' => $bullishPatterns,

                'bearish_patterns' => $bearishPatterns,

                'market_sentiment' => $this->calculateMarketSentiment($bullishPatterns, $bearishPatterns),

                'sureshot_analysis' => $sureshotAnalysis,

                'analysis_type' => 'COMPREHENSIVE_PATTERN_ANALYSIS',

                'analyzed_at' => now()

            ];

        });

    }



    /**

     * Enhanced SURESHOT detection

     */

    public function detectSureshot(Stock $stock, array $patterns = null): array

    {

        if ($patterns === null) {

            $patterns = collect($stock->patterns);

        } else {

            $patterns = collect($patterns);

        }

        

        $criteria = [

            'multiple_timeframe_alignment' => $this->checkMultipleTimeframeAlignment($patterns),

            'high_success_probability' => $patterns->where('success_probability', '>', 85)->count() >= 2,

            'excellent_reliability' => $patterns->where('reliability', '>', 80)->count() >= 1,

            'strong_pattern_strength' => $patterns->where('rank', 1)->count() >= 1,

            'favorable_risk_reward' => $this->safeDecimalCast($patterns->avg('risk_reward')) > 2.5,

            'pattern_diversity' => $patterns->pluck('pattern_category')->unique()->count() >= 2,

            'bullish_alignment' => $patterns->where('bullish_probability', '>', 70)->count() >= 2,

            'premium_patterns' => $this->checkPremiumPatterns($patterns),

            'volume_confirmation' => $this->safeDecimalCast($stock->volume ?? 0) > 1000000,

            'market_cap_stability' => $this->safeDecimalCast($stock->market_cap ?? 0) > 10000

        ];



        $metCriteria = count(array_filter($criteria));

        $totalCriteria = count($criteria);

        $sureshotScore = $totalCriteria > 0 ? ($metCriteria / $totalCriteria) * 100 : 0;



        $isSureshot = $sureshotScore >= 70;

        $isUltraSureshot = $sureshotScore >= 85;



        return [

            'is_sureshot' => $isSureshot,

            'is_ultra_sureshot' => $isUltraSureshot,

            'sureshot_score' => $this->safeDecimalCast(round($sureshotScore, 1)),

            'confidence' => $this->safeDecimalCast(min(98, max(50, $sureshotScore + rand(-3, 8)))),

            'criteria_analysis' => $criteria,

            'criteria_met' => $metCriteria,

            'total_criteria' => $totalCriteria,

            'pattern_strength' => $this->calculatePatternStrength($patterns),

            'risk_assessment' => $this->assessOverallRisk($patterns),

            'detected_at' => now(),

            'valid_until' => now()->addDays(3)

        ];

    }



    private function convertReliabilityToNumeric(string $reliability): float

    {

        $numericValue = match($reliability) {

            'exceptional' => 95,

            'excellent' => 88,

            'very_good' => 82,

            'good' => 75,

            'average' => 68,

            'poor' => 50,

            default => 60

        };

        

        return $this->safeDecimalCast($numericValue);

    }



    private function calculateDetectionProbability(Stock $stock, string $timeframe): int

    {

        $baseProbability = 75;

        

        $volume = $this->safeDecimalCast($stock->volume ?? 0);

        if ($volume > 5000000) $baseProbability += 10;

        elseif ($volume < 100000) $baseProbability -= 15;

        

        $volatility = abs($this->safeDecimalCast($stock->price_change_percent ?? 0));

        if ($volatility > 3) $baseProbability += 5;

        if ($volatility > 7) $baseProbability -= 10;

        

        return max(50, min(95, $baseProbability));

    }



    private function selectOptimalPattern(Stock $stock, string $timeframe): string

    {

        $patterns = array_keys($this->patternTypes);

        

        $marketCap = $this->safeDecimalCast($stock->market_cap ?? 0);

        if ($marketCap > 100000) {

            $highReliabilityPatterns = array_filter($patterns, function($pattern) {

                return $this->patternTypes[$pattern]['reliability'] >= 85;

            });

            

            if (!empty($highReliabilityPatterns)) {

                return $highReliabilityPatterns[array_rand($highReliabilityPatterns)];

            }

        }

        

        return $patterns[array_rand($patterns)];

    }



    private function calculatePatternRank(Stock $stock, string $timeframe, array $patternConfig): int

    {

        $score = $this->safeDecimalCast($patternConfig['reliability']);

        

        $marketCap = $this->safeDecimalCast($stock->market_cap ?? 0);

        if ($marketCap > 100000) $score += 5;

        if ($patternConfig['risk_level'] === 'low') $score += 3;

        

        return match(true) {

            $score >= 90 => 1,

            $score >= 87 => 2,

            $score >= 83 => 3,

            $score >= 80 => 4,

            default => 5

        };

    }



    private function getEnhancedMarketAdjustment(Stock $stock, string $timeframe): float

    {

        $adjustment = 0;

        

        $marketCap = $this->safeDecimalCast($stock->market_cap ?? 0);

        if ($marketCap > 200000) $adjustment += 4;

        elseif ($marketCap > 50000) $adjustment += 2;

        elseif ($marketCap < 5000) $adjustment -= 2;

        

        $sectorBonus = match($stock->sector ?? '') {

            'Information Technology' => 4,

            'Banking' => 3,

            'Pharmaceuticals' => 3,

            'FMCG' => 2,

            default => 0

        };

        

        $timeframeBonus = match($timeframe) {

            'MONTHLY' => 3,

            'WEEKLY' => 2,

            'DAILY' => 2,

            'HOURLY' => 1,

            default => 0

        };

        

        return $this->safeDecimalCast($adjustment + $sectorBonus + $timeframeBonus);

    }



    private function getVolumeAdjustment(Stock $stock): float

    {

        $volume = $this->safeDecimalCast($stock->volume ?? 0);

        

        $adjustment = match(true) {

            $volume > 10000000 => 3,

            $volume > 5000000 => 2,

            $volume > 1000000 => 1,

            $volume > 100000 => 0,

            default => -2

        };

        

        return $this->safeDecimalCast($adjustment);

    }



    private function getReliabilityGrade(float $success): string

    {

        $safeSuccess = $this->safeDecimalCast($success);

        

        return match(true) {

            $safeSuccess >= 92 => 'exceptional',

            $safeSuccess >= 88 => 'excellent',

            $safeSuccess >= 82 => 'very_good',

            $safeSuccess >= 75 => 'good',

            $safeSuccess >= 68 => 'average',

            default => 'poor'

        };

    }



    private function calculateRiskReward(float $return, string $riskLevel): float

    {

        $safeReturn = $this->safeDecimalCast($return);

        $baseRiskReward = $safeReturn / 10;

        

        $multiplier = match($riskLevel) {

            'low' => 1.5,

            'medium' => 1.2,

            'high' => 0.8,

            default => 1.0

        };

        

        return $this->safeDecimalCast($baseRiskReward * $multiplier);

    }



    private function getRecommendation(float $score, int $patternCount, int $bullishPatterns, int $bearishPatterns): string

    {

        $safeScore = $this->safeDecimalCast($score);

        

        if ($safeScore > 92 && $patternCount >= 4 && $bullishPatterns >= 3) {

            return 'ULTRA SURESHOT BUY';

        }

        

        if ($safeScore > 88 && $patternCount >= 3 && $bullishPatterns >= 2) {

            return 'SURESHOT BUY';

        }

        

        $bullishRatio = $patternCount > 0 ? $bullishPatterns / $patternCount : 0;

        $bearishRatio = $patternCount > 0 ? $bearishPatterns / $patternCount : 0;

        

        return match(true) {

            $safeScore >= 85 && $bullishRatio > 0.6 => 'STRONG BUY',

            $safeScore >= 78 && $bullishRatio > 0.5 => 'BUY',

            $safeScore >= 68 && $bullishRatio > 0.4 => 'MODERATE BUY',

            $safeScore >= 55 && $bearishRatio < 0.6 => 'HOLD',

            $safeScore >= 40 => 'WEAK HOLD',

            $bearishRatio > 0.7 => 'STRONG SELL',

            default => 'NEUTRAL'

        };

    }



    private function calculateMarketSentiment(int $bullishPatterns, int $bearishPatterns): string

    {

        $total = $bullishPatterns + $bearishPatterns;

        if ($total === 0) return 'NEUTRAL';

        

        $bullishRatio = $bullishPatterns / $total;

        

        return match(true) {

            $bullishRatio > 0.75 => 'VERY_BULLISH',

            $bullishRatio > 0.6 => 'BULLISH',

            $bullishRatio > 0.4 => 'NEUTRAL',

            $bullishRatio > 0.25 => 'BEARISH',

            default => 'VERY_BEARISH'

        };

    }



    private function checkMultipleTimeframeAlignment($patterns): bool

    {

        $timeframes = $patterns->pluck('timeframe')->unique();

        return $timeframes->count() >= 3;

    }



    private function checkPremiumPatterns($patterns): bool

    {

        $premiumPatterns = [

            'Cup and Handle Chart Pattern',

            'Head and Shoulders Chart Pattern',

            'Inverse Head and Shoulders Pattern'

        ];

        

        return $patterns->whereIn('pattern_name', $premiumPatterns)->count() >= 1;

    }



    private function calculatePatternStrength($patterns): string

    {

        if ($patterns->isEmpty()) return 'NONE';

        

        $avgSuccess = $this->safeDecimalCast($patterns->avg('success_probability'));

        return match(true) {

            $avgSuccess > 90 => 'EXCEPTIONAL',

            $avgSuccess > 85 => 'VERY_STRONG',

            $avgSuccess > 75 => 'STRONG',

            $avgSuccess > 65 => 'MODERATE',

            default => 'WEAK'

        };

    }



    private function assessOverallRisk($patterns): string

    {

        if ($patterns->isEmpty()) return 'UNKNOWN';

        

        $riskLevels = $patterns->pluck('risk_level');

        $highRisk = $riskLevels->filter(fn($r) => $r === 'high')->count();

        $total = $riskLevels->count();

        

        $highRiskPercentage = $total > 0 ? ($highRisk / $total) * 100 : 0;

        

        return match(true) {

            $highRiskPercentage > 60 => 'VERY_HIGH',

            $highRiskPercentage > 40 => 'HIGH',

            $highRiskPercentage > 20 => 'MODERATE',

            default => 'LOW'

        };

    }



    private function fetchHistoricalData(Stock $stock): array

    {

        try {

            $client = new \GuzzleHttp\Client([

                'timeout' => 30, // Set a 30-second timeout

                'connect_timeout' => 10, // Set a 10-second connection timeout

            ]);



            $cacheKey = "historical_data_{$stock->symbol}";

            // Check cache first

            if (Cache::has($cacheKey)) {

                return Cache::get($cacheKey);

            }



            $response = $client->get('https://stock-history-api-dh13.onrender.com/stock-data', [

                'query' => ['symbol' => $stock->symbol]

            ]);



            $data = json_decode($response->getBody(), true);



            if (!isset($data['success']) || !$data['success']) {

                throw new Exception($data['error'] ?? 'Failed to fetch historical data');

            }



            // Cache the data for 1 hour

            Cache::put($cacheKey, $data['timeframes'], 3600);



            return $data['timeframes'];

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            Log::error("Failed to fetch historical data for {$stock->symbol}: " . $e->getMessage());

            return [];

        } catch (Exception $e) {

            Log::error("Failed to fetch historical data for {$stock->symbol}: " . $e->getMessage());

            return [];

        }

    }



    // Helper methods for common tasks

    private function findHighs(array $ohlcData): array

    {

        $highs = [];

        foreach ($ohlcData as $i => $data) {

            if ($i > 0 && $i < count($ohlcData) - 1) {

                $prevHigh = $this->safeDecimalCast($ohlcData[$i - 1]['high']);

                $currentHigh = $this->safeDecimalCast($data['high']);

                $nextHigh = $this->safeDecimalCast($ohlcData[$i + 1]['high']);

                if ($currentHigh > $prevHigh && $currentHigh > $nextHigh) {

                    $highs[] = ['index' => $i, 'price' => $currentHigh, 'volume' => $data['volume']];

                }

            }

        }

        return $highs;

    }



    private function findLows(array $ohlcData): array

    {

        $lows = [];

        foreach ($ohlcData as $i => $data) {

            if ($i > 0 && $i < count($ohlcData) - 1) {

                $prevLow = $this->safeDecimalCast($ohlcData[$i - 1]['low']);

                $currentLow = $this->safeDecimalCast($data['low']);

                $nextLow = $this->safeDecimalCast($ohlcData[$i + 1]['low']);

                if ($currentLow < $prevLow && $currentLow < $nextLow) {

                    $lows[] = ['index' => $i, 'price' => $currentLow, 'volume' => $data['volume']];

                }

            }

        }

        return $lows;

    }



    private function validateOhlcData(array $ohlcData, int $minLength, string $patternName): bool

    {

        if (count($ohlcData) < $minLength || empty($ohlcData)) {

            Log::warning("Insufficient data for {$patternName}: " . count($ohlcData) . " points");

            return false;

        }

        foreach ($ohlcData as $data) {

            if (!isset($data['open'], $data['high'], $data['low'], $data['close'], $data['volume'])) {

                Log::warning("Invalid OHLC data structure for {$patternName}");

                return false;

            }

        }

        return true;

    }



    /**

     * Detect Double Bottom pattern

     */

    // private function detectDoubleBottom(array $ohlcData, array $patternConfig): ?array

    // {

    //     if (!$this->validateOhlcData($ohlcData, 20, 'Double Bottom')) {

    //         return null;

    //     }



    //     $lows = $this->findLows($ohlcData);

    //     $threshold = 0.02; // 2% price similarity for lows

    //     $minDistance = 5; // Minimum bars between lows



    //     for ($i = 1; $i < count($lows); $i++) {

    //         $low1 = $lows[$i - 1];

    //         $low2 = $lows[$i];



    //         if ($low2['index'] - $low1['index'] < $minDistance) {

    //             continue;

    //         }



    //         if (abs($low1['price'] - $low2['price']) / $low1['price'] < $threshold) {

    //             $slice = array_slice($ohlcData, $low1['index'], $low2['index'] - $low1['index']);

    //             if (empty($slice)) {

    //                 continue;

    //             }



    //             $neckline = max(array_column($slice, 'high'));

    //             $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

    //             $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



    //             if ($recentClose > $neckline && $recentVolume > $low1['volume'] && $recentVolume > $low2['volume']) {

    //                 return [

    //                     'pattern_name' => 'Double Bottom Chart Pattern',

    //                     'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

    //                     'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

    //                     'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

    //                     'pattern_category' => $patternConfig['category'],

    //                     'risk_level' => $patternConfig['risk_level']

    //                 ];

    //             }

    //         }

    //     }



    //     return null;

    // }

    private function detectDoubleBottom(array $ohlcData, array $patternConfig): ?array
{
    // Require at least 20 candles
    if (!$this->validateOhlcData($ohlcData, 20, 'Double Bottom')) {
        return null;
    }

    // Ensure there’s a preceding downtrend
    if (!$this->isDowntrend($ohlcData, 20)) {
        return null;
    }

    $lows = $this->findLows($ohlcData);
    $threshold = $patternConfig['low_similarity_threshold'] ?? 0.03; // 3% default
    $minDistance = $patternConfig['min_distance'] ?? 5;              // 5 bars apart
    $breakoutMargin = $patternConfig['breakout_margin'] ?? 0.005;    // 0.5% above neckline

    // Calculate average breakout volume (last 10 bars)
    $lastBars = array_slice($ohlcData, -10);
    $avgVolume = array_sum(array_column($lastBars, 'volume')) / max(1, count($lastBars));

    for ($i = 1; $i < count($lows); $i++) {
        $low1 = $lows[$i - 1];
        $low2 = $lows[$i];

        // Enforce min distance between lows
        if ($low2['index'] - $low1['index'] < $minDistance) {
            continue;
        }

        // Check price similarity of lows
        if (abs($low1['price'] - $low2['price']) / $low1['price'] < $threshold) {
            // Look between the lows for the neckline
            $slice = array_slice($ohlcData, $low1['index'], $low2['index'] - $low1['index']);
            if (empty($slice)) {
                continue;
            }

            $neckline = max(array_column($slice, 'high'));
            $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);
            $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);

            // Require breakout above neckline + small margin
            if ($recentClose > ($neckline * (1 + $breakoutMargin)) && $recentVolume > $avgVolume) {
                return [
                    'pattern_name'        => 'Double Bottom Chart Pattern',
                    'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),
                    'avg_return'          => $this->safeDecimalCast($patternConfig['avg_return']),
                    'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),
                    'pattern_category'    => $patternConfig['category'],
                    'risk_level'          => $patternConfig['risk_level'],
                    'details' => [
                        'low1_index' => $low1['index'],
                        'low2_index' => $low2['index'],
                        'low1_price' => $low1['price'],
                        'low2_price' => $low2['price'],
                        'neckline'   => $neckline,
                        'breakout_close' => $recentClose,
                        'breakout_volume' => $recentVolume,
                        'avg_volume' => $avgVolume
                    ]
                ];
            }
        }
    }

    return null;
}


/**
 * Check if the stock is in a downtrend
 * A downtrend = lower highs or price slope going down
 */
private function isDowntrend(array $ohlcData, int $lookback = 20): bool
{
    if (count($ohlcData) < $lookback) {
        return false;
    }

    $slice = array_slice($ohlcData, -$lookback);

    $firstClose = $this->safeDecimalCast(reset($slice)['close']);
    $lastClose  = $this->safeDecimalCast(end($slice)['close']);

    // Basic check: last price must be lower than starting point
    if ($lastClose >= $firstClose) {
        return false;
    }

    // Optional: check slope of closes
    $closes = array_column($slice, 'close');
    $n = count($closes);
    $sumX = $sumY = $sumXY = $sumXX = 0;

    for ($i = 0; $i < $n; $i++) {
        $sumX  += $i;
        $sumY  += $closes[$i];
        $sumXY += $i * $closes[$i];
        $sumXX += $i * $i;
    }

    // Linear regression slope
    $slope = ($n * $sumXY - $sumX * $sumY) / max(1, ($n * $sumXX - $sumX * $sumX));

    return $slope < 0; // Negative slope = downtrend
}



    /**

     * Detect Head and Shoulders pattern

     */

    private function detectHeadAndShoulders(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 30, 'Head and Shoulders')) {

            return null;

        }



        $peaks = $this->findHighs($ohlcData);

        $threshold = 0.02; // 2% price similarity for shoulders

        $minDistance = 5; // Minimum bars between peaks



        for ($i = 2; $i < count($peaks); $i++) {

            $leftShoulder = $peaks[$i - 2];

            $head = $peaks[$i - 1];

            $rightShoulder = $peaks[$i];



            if ($rightShoulder['index'] - $leftShoulder['index'] < $minDistance * 2) {

                continue;

            }



            if ($head['price'] > $leftShoulder['price'] && $head['price'] > $rightShoulder['price'] &&

                abs($leftShoulder['price'] - $rightShoulder['price']) / $leftShoulder['price'] < $threshold) {

                $slice = array_slice($ohlcData, $leftShoulder['index'], $rightShoulder['index'] - $leftShoulder['index']);

                if (empty($slice)) {

                    continue;

                }



                $neckline = min(array_column($slice, 'low'));

                $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

                $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



                if ($recentClose < $neckline && $recentVolume > $leftShoulder['volume'] && $recentVolume > $rightShoulder['volume']) {

                    return [

                        'pattern_name' => 'Head and Shoulders Chart Pattern',

                        'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                        'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                        'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                        'pattern_category' => $patternConfig['category'],

                        'risk_level' => $patternConfig['risk_level']

                    ];

                }

            }

        }



        return null;

    }



    /**

     * Detect Cup and Handle pattern

     */

    private function detectCupAndHandle(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 30, 'Cup and Handle')) {

            return null;

        }



        $lows = $this->findLows($ohlcData);

        $highs = $this->findHighs($ohlcData);

        $threshold = 0.05; // 5% price depth for cup

        $minCupLength = 10; // Minimum bars for cup

        $maxHandleLength = 10; // Maximum bars for handle



        for ($i = 1; $i < count($lows) - 1; $i++) {

            $cupLow = $lows[$i];

            $cupStart = null;

            $cupEnd = null;



            foreach ($highs as $high) {

                if ($high['index'] < $cupLow['index'] && (!$cupStart || $high['index'] > $cupStart['index'])) {

                    $cupStart = $high;

                }

                if ($high['index'] > $cupLow['index'] && (!$cupEnd || $high['index'] < $cupEnd['index'])) {

                    $cupEnd = $high;

                }

            }



            if ($cupStart && $cupEnd &&

                abs($cupStart['index'] - $cupEnd['index']) >= $minCupLength &&

                abs($cupStart['price'] - $cupEnd['price']) / $cupStart['price'] < $threshold &&

                $cupLow['price'] < $cupStart['price'] * (1 - $threshold)) {

                $handleData = array_slice($ohlcData, $cupEnd['index'], $maxHandleLength);

                if (count($handleData) >= 3) {

                    $handleHighs = array_column($handleData, 'high');

                    $handleLows = array_column($handleData, 'low');

                    $maxHigh = max($handleHighs);

                    $minLow = min($handleLows);



                    if ($maxHigh <= $cupEnd['price'] && ($maxHigh - $minLow) / $cupEnd['price'] < 0.1) {

                        $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

                        $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



                        if ($recentClose > $cupEnd['price'] && $recentVolume > $cupEnd['volume']) {

                            return [

                                'pattern_name' => 'Cup and Handle Chart Pattern',

                                'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                                'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                                'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                                'pattern_category' => $patternConfig['category'],

                                'risk_level' => $patternConfig['risk_level']

                            ];

                        }

                    }

                }

            }

        }



        return null;

    }



    /**

     * Detect Ascending Triangle pattern

     */

    private function detectAscendingTriangle(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 20, 'Ascending Triangle')) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $lows = $this->findLows($ohlcData);

        $threshold = 0.02; // 2% price similarity for resistance

        $minDistance = 5; // Minimum bars between touches



        $resistanceHighs = array_filter($highs, function ($high) use ($highs, $threshold) {

            $firstHigh = $highs[0]['price'];

            return abs($high['price'] - $firstHigh) / $firstHigh < $threshold;

        });



        if (count($resistanceHighs) >= 2 && count($lows) > 1 && $lows[count($lows) - 1]['price'] > $lows[0]['price']) {

            $resistance = max(array_column($resistanceHighs, 'price'));

            $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

            $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



            if ($recentClose > $resistance && $recentVolume > $resistanceHighs[count($resistanceHighs) - 1]['volume']) {

                return [

                    'pattern_name' => 'Ascending Triangle Chart Pattern',

                    'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                    'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                    'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                    'pattern_category' => $patternConfig['category'],

                    'risk_level' => $patternConfig['risk_level']

                ];

            }

        }



        return null;

    }



    /**

     * Detect Descending Triangle pattern

     */

    private function detectDescendingTriangle(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 20, 'Descending Triangle')) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $lows = $this->findLows($ohlcData);

        $threshold = 0.02; // 2% price similarity for support

        $minDistance = 5; // Minimum bars between touches



        $supportLows = array_filter($lows, function ($low) use ($lows, $threshold) {

            $firstLow = $lows[0]['price'];

            return abs($low['price'] - $firstLow) / $firstLow < $threshold;

        });



        if (count($supportLows) >= 2 && count($highs) > 1 && $highs[count($highs) - 1]['price'] < $highs[0]['price']) {

            $support = min(array_column($supportLows, 'price'));

            $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

            $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



            if ($recentClose < $support && $recentVolume > $supportLows[count($supportLows) - 1]['volume']) {

                return [

                    'pattern_name' => 'Descending Triangle Chart Pattern',

                    'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                    'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                    'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                    'pattern_category' => $patternConfig['category'],

                    'risk_level' => $patternConfig['risk_level']

                ];

            }

        }



        return null;

    }



    /**

     * Detect Symmetrical Triangle pattern

     */

    private function detectSymmetricalTriangle(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 20, 'Symmetrical Triangle')) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $lows = $this->findLows($ohlcData);

        $threshold = 0.02; // 2% price similarity for touches

        $minDistance = 5; // Minimum bars between touches



        $resistanceHighs = array_filter($highs, function ($high) use ($highs, $threshold) {

            $firstHigh = $highs[0]['price'];

            return abs($high['price'] - $firstHigh) / $firstHigh < $threshold;

        });



        $supportLows = array_filter($lows, function ($low) use ($lows, $threshold) {

            $firstLow = $lows[0]['price'];

            return abs($low['price'] - $firstLow) / $firstLow < $threshold;

        });



        if (count($resistanceHighs) >= 2 && count($supportLows) >= 2 &&

            $highs[count($highs) - 1]['price'] < $highs[0]['price'] &&

            $lows[count($lows) - 1]['price'] > $lows[0]['price']) {

            $resistance = max(array_column($resistanceHighs, 'price'));

            $support = min(array_column($supportLows, 'price'));

            $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

            $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



            if ($recentClose > $resistance && $recentVolume > $resistanceHighs[count($resistanceHighs) - 1]['volume']) {

                return [

                    'pattern_name' => 'Symmetrical Triangle Chart Pattern',

                    'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                    'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                    'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                    'pattern_category' => $patternConfig['category'],

                    'risk_level' => $patternConfig['risk_level']

                ];

            }

        }



        return null;

    }



    /**

     * Detect Pennant pattern

     */

    private function detectPennant(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 15, 'Pennant')) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $lows = $this->findLows($ohlcData);

        $threshold = 0.03; // 3% price range for consolidation

        $minPoleLength = 5; // Minimum bars for flagpole

        $maxPennantLength = 10; // Maximum bars for pennant



        if (count($highs) >= 2 && count($lows) >= 2) {

            $poleHigh = $highs[0];

            $poleLow = $lows[0];

            $poleLength = abs($poleHigh['index'] - $poleLow['index']);



            if ($poleLength >= $minPoleLength) {

                $pennantData = array_slice($ohlcData, $poleHigh['index'], $maxPennantLength);

                if (count($pennantData) >= 3) {

                    $pennantHighs = array_column($pennantData, 'high');

                    $pennantLows = array_column($pennantData, 'low');

                    $maxHigh = max($pennantHighs);

                    $minLow = min($pennantLows);



                    if ($maxHigh - $minLow < $poleHigh['price'] * $threshold) {

                        $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

                        $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



                        if ($recentClose > $maxHigh && $recentVolume > $poleHigh['volume']) {

                            return [

                                'pattern_name' => 'Pennant Chart Pattern',

                                'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                                'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                                'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                                'pattern_category' => $patternConfig['category'],

                                'risk_level' => $patternConfig['risk_level']

                            ];

                        }

                    }

                }

            }

        }



        return null;

    }



    /**

     * Detect Bullish Flag pattern

     */

    private function detectBullishFlag(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 15, 'Bullish Flag')) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $lows = $this->findLows($ohlcData);

        $threshold = 0.05; // 5% consolidation range

        $minPoleLength = 5; // Minimum bars for pole

        $maxFlagLength = 10; // Maximum bars for flag



        if (count($highs) >= 2 && count($lows) >= 2) {

            $poleStart = $lows[0];

            $poleEnd = $highs[0];

            $poleLength = $poleEnd['index'] - $poleStart['index'];



            if ($poleLength >= $minPoleLength && $poleEnd['price'] > $poleStart['price'] * 1.1) { // At least 10% upward move for pole

                $flagData = array_slice($ohlcData, $poleEnd['index'], $maxFlagLength);

                if (count($flagData) >= 3) {

                    $flagHighs = array_column($flagData, 'high');

                    $flagLows = array_column($flagData, 'low');

                    $maxHigh = max($flagHighs);

                    $minLow = min($flagLows);



                    if (($maxHigh - $minLow) / $poleEnd['price'] < $threshold && max($flagLows) < $poleEnd['price']) { // Downward sloping flag

                        $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

                        $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



                        if ($recentClose > $maxHigh && $recentVolume > $poleEnd['volume']) {

                            return [

                                'pattern_name' => 'Bullish Flag Chart Pattern',

                                'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                                'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                                'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                                'pattern_category' => $patternConfig['category'],

                                'risk_level' => $patternConfig['risk_level']

                            ];

                        }

                    }

                }

            }

        }



        return null;

    }



    /**

     * Detect Bearish Flag pattern

     */

    private function detectBearishFlag(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 15, 'Bearish Flag')) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $lows = $this->findLows($ohlcData);

        $threshold = 0.05; // 5% consolidation range

        $minPoleLength = 5; // Minimum bars for pole

        $maxFlagLength = 10; // Maximum bars for flag



        if (count($highs) >= 2 && count($lows) >= 2) {

            $poleStart = $highs[0];

            $poleEnd = $lows[0];

            $poleLength = $poleEnd['index'] - $poleStart['index'];



            if ($poleLength >= $minPoleLength && $poleEnd['price'] < $poleStart['price'] * 0.9) { // At least 10% downward move for pole

                $flagData = array_slice($ohlcData, $poleEnd['index'], $maxFlagLength);

                if (count($flagData) >= 3) {

                    $flagHighs = array_column($flagData, 'high');

                    $flagLows = array_column($flagData, 'low');

                    $maxHigh = max($flagHighs);

                    $minLow = min($flagLows);



                    if (($maxHigh - $minLow) / $poleEnd['price'] < $threshold && min($flagHighs) > $poleEnd['price']) { // Upward sloping flag

                        $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

                        $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



                        if ($recentClose < $minLow && $recentVolume > $poleEnd['volume']) {

                            return [

                                'pattern_name' => 'Bearish Flag Chart Pattern',

                                'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                                'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                                'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                                'pattern_category' => $patternConfig['category'],

                                'risk_level' => $patternConfig['risk_level']

                            ];

                        }

                    }

                }

            }

        }



        return null;

    }



    /**

     * Detect Rising Wedge pattern

     */

    private function detectRisingWedge(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 20, 'Rising Wedge')) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $lows = $this->findLows($ohlcData);

        $threshold = 0.03; // 3% for converging lines

        $minPoints = 2; // At least 2 highs and lows



        if (count($highs) >= $minPoints && count($lows) >= $minPoints) {

            $lastHigh = end($highs)['price'];

            $lastLow = end($lows)['price'];

            $firstHigh = $highs[0]['price'];

            $firstLow = $lows[0]['price'];



            if ($lastHigh < $firstHigh && $lastLow < $firstLow && ($lastHigh - $lastLow) < ($firstHigh - $firstLow) * $threshold) { // Converging upward

                $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

                $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



                if ($recentClose < $lastLow && $recentVolume > end($lows)['volume']) {

                    return [

                        'pattern_name' => 'Rising Wedge Chart Pattern',

                        'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                        'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                        'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                        'pattern_category' => $patternConfig['category'],

                        'risk_level' => $patternConfig['risk_level']

                    ];

                }

            }

        }



        return null;

    }



    /**

     * Detect Falling Wedge pattern

     */

    private function detectFallingWedge(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 20, 'Falling Wedge')) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $lows = $this->findLows($ohlcData);

        $threshold = 0.03; // 3% for converging lines

        $minPoints = 2; // At least 2 highs and lows



        if (count($highs) >= $minPoints && count($lows) >= $minPoints) {

            $lastHigh = end($highs)['price'];

            $lastLow = end($lows)['price'];

            $firstHigh = $highs[0]['price'];

            $firstLow = $lows[0]['price'];



            if ($lastHigh > $firstHigh && $lastLow > $firstLow && ($lastHigh - $lastLow) < ($firstHigh - $firstLow) * $threshold) { // Converging downward

                $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

                $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



                if ($recentClose > $lastHigh && $recentVolume > end($highs)['volume']) {

                    return [

                        'pattern_name' => 'Falling Wedge Chart Pattern',

                        'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                        'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                        'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                        'pattern_category' => $patternConfig['category'],

                        'risk_level' => $patternConfig['risk_level']

                    ];

                }

            }

        }



        return null;

    }



    /**

     * Detect Triple Top pattern

     */

    private function detectTripleTop(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 30, 'Triple Top')) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $threshold = 0.02; // 2% price similarity for highs

        $minDistance = 5; // Minimum bars between highs



        if (count($highs) >= 3) {

            $high1 = $highs[count($highs) - 3];

            $high2 = $highs[count($highs) - 2];

            $high3 = $highs[count($highs) - 1];



            if (abs($high1['index'] - $high2['index']) >= $minDistance && abs($high2['index'] - $high3['index']) >= $minDistance &&

                abs($high1['price'] - $high2['price']) / $high1['price'] < $threshold &&

                abs($high2['price'] - $high3['price']) / $high2['price'] < $threshold) {

                $slice1 = array_slice($ohlcData, $high1['index'], $high3['index'] - $high1['index']);

                if (empty($slice1)) {

                    return null;

                }



                $neckline = min(array_column($slice1, 'low'));

                $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

                $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



                if ($recentClose < $neckline && $recentVolume > $high3['volume']) {

                    return [

                        'pattern_name' => 'Triple Top Chart Patterns',

                        'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                        'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                        'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                        'pattern_category' => $patternConfig['category'],

                        'risk_level' => $patternConfig['risk_level']

                    ];

                }

            }

        }



        return null;

    }



    /**

     * Detect Triple Bottom pattern

     */

    private function detectTripleBottom(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 30, 'Triple Bottom')) {

            return null;

        }



        $lows = $this->findLows($ohlcData);

        $threshold = 0.02; // 2% price similarity for lows

        $minDistance = 5; // Minimum bars between lows



        if (count($lows) >= 3) {

            $low1 = $lows[count($lows) - 3];

            $low2 = $lows[count($lows) - 2];

            $low3 = $lows[count($lows) - 1];



            if (abs($low1['index'] - $low2['index']) >= $minDistance && abs($low2['index'] - $low3['index']) >= $minDistance &&

                abs($low1['price'] - $low2['price']) / $low1['price'] < $threshold &&

                abs($low2['price'] - $low3['price']) / $low2['price'] < $threshold) {

                $slice1 = array_slice($ohlcData, $low1['index'], $low3['index'] - $low1['index']);

                if (empty($slice1)) {

                    return null;

                }



                $neckline = max(array_column($slice1, 'high'));

                $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

                $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



                if ($recentClose > $neckline && $recentVolume > $low3['volume']) {

                    return [

                        'pattern_name' => 'Triple Bottom Chart Pattern',

                        'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                        'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                        'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                        'pattern_category' => $patternConfig['category'],

                        'risk_level' => $patternConfig['risk_level']

                    ];

                }

            }

        }



        return null;

    }



    /**

     * Improved generic pattern detection for remaining patterns

     */

    private function detectGenericPattern(array $ohlcData, array $patternConfig, string $patternName): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 20, $patternName)) {

            return null;

        }



        $highs = $this->findHighs($ohlcData);

        $lows = $this->findLows($ohlcData);

        $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);

        $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



        // Improved condition: Detect if there's a breakout (up for bullish, down for bearish) with volume

        $bullishProb = $patternConfig['bullish_probability'];

        $lastHigh = end($highs)['price'] ?? 0;

        $lastLow = end($lows)['price'] ?? 0;

        $lastVolume = end($highs)['volume'] ?? 0;



        if (count($highs) >= 2 && count($lows) >= 2) {

            if ($bullishProb > 50 && $recentClose > $lastHigh && $recentVolume > $lastVolume) {

                return [

                    'pattern_name' => $patternName,

                    'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                    'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                    'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                    'pattern_category' => $patternConfig['category'],

                    'risk_level' => $patternConfig['risk_level']

                ];

            } elseif ($bullishProb < 50 && $recentClose < $lastLow && $recentVolume > $lastVolume) {

                return [

                    'pattern_name' => $patternName,

                    'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                    'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                    'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                    'pattern_category' => $patternConfig['category'],

                    'risk_level' => $patternConfig['risk_level']

                ];

            } elseif ($bullishProb == 50) {

                // For neutral patterns, check for significant volatility or breakout in either direction

                $volatility = abs($recentClose - $ohlcData[count($ohlcData)-2]['close'] ?? 0) / ($ohlcData[count($ohlcData)-2]['close'] ?? 1);

                if ($volatility > 0.03 && $recentVolume > $lastVolume) { // 3% move with volume

                    return [

                        'pattern_name' => $patternName,

                        'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                        'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                        'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                        'pattern_category' => $patternConfig['category'],

                        'risk_level' => $patternConfig['risk_level']

                    ];

                }

            }

        }



        return null;

    }



    /**

     * Detect Double Top pattern using historical data

     */

    private function detectDoubleTop(array $ohlcData, array $patternConfig): ?array

    {

        // Validate data length and structure

        if (count($ohlcData) < 20 || empty($ohlcData)) {

            return null;

        }



        // Validate required keys in each data point

        foreach ($ohlcData as $data) {

            if (!isset($data['open'], $data['high'], $data['low'], $data['close'], $data['volume'])) {

                Log::warning("Invalid OHLC data structure for Double Top");

                return null;

            }

        }



        $highs = [];

        $threshold = 0.02; // 2% price similarity for highs

        $minDistance = 5; // Minimum bars between highs



        // Find local highs

        foreach ($ohlcData as $i => $data) {

            if ($i > 0 && $i < count($ohlcData) - 1) {

                $prevHigh = $this->safeDecimalCast($ohlcData[$i - 1]['high']);

                $currentHigh = $this->safeDecimalCast($data['high']);

                $nextHigh = $this->safeDecimalCast($ohlcData[$i + 1]['high']);



                if ($currentHigh > $prevHigh && $currentHigh > $nextHigh) {

                    $highs[] = ['index' => $i, 'price' => $currentHigh, 'volume' => $data['volume']];

                }

            }

        }



        // Check for Double Top

        for ($i = 1; $i < count($highs); $i++) {

            $high1 = $highs[$i - 1];

            $high2 = $highs[$i];



            // Ensure valid indices and minimum distance

            if ($high2['index'] - $high1['index'] < $minDistance) {

                continue;

            }



            if (abs($high1['price'] - $high2['price']) / $high1['price'] < $threshold) {

                // Extract slice safely

                $slice = array_slice($ohlcData, $high1['index'], $high2['index'] - $high1['index']);

                if (empty($slice)) {

                    continue;

                }



                $neckline = min(array_column($slice, 'low'));

                $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);



                if ($recentClose < $neckline) {

                    $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);

                    if ($recentVolume > $high1['volume'] && $recentVolume > $high2['volume']) {

                        return [

                            'pattern_name' => 'Double Top Chart Pattern',

                            'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                            'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                            'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                            'pattern_category' => $patternConfig['category'],

                            'risk_level' => $patternConfig['risk_level']

                        ];

                    }

                }

            }

        }



        return null;

    }



    /**

     * Detect Inverse Head and Shoulders pattern using historical data

     */

    private function detectInverseHeadAndShoulders(array $ohlcData, array $patternConfig): ?array

    {

        // Validate data length and structure

        if (count($ohlcData) < 30 || empty($ohlcData)) {

            return null;

        }



        // Validate required keys in each data point

        foreach ($ohlcData as $data) {

            if (!isset($data['open'], $data['high'], $data['low'], $data['close'], $data['volume'])) {

                Log::warning("Invalid OHLC data structure for Inverse Head and Shoulders");

                return null;

            }

        }



        $lows = [];

        $threshold = 0.02; // 2% price similarity for shoulders

        $minDistance = 5; // Minimum bars between lows



        // Find local lows

        foreach ($ohlcData as $i => $data) {

            if ($i > 0 && $i < count($ohlcData) - 1) {

                $prevLow = $this->safeDecimalCast($ohlcData[$i - 1]['low']);

                $currentLow = $this->safeDecimalCast($data['low']);

                $nextLow = $this->safeDecimalCast($ohlcData[$i + 1]['low']);



                if ($currentLow < $prevLow && $currentLow < $nextLow) {

                    $lows[] = ['index' => $i, 'price' => $currentLow, 'volume' => $data['volume']];

                }

            }

        }



        // Check for Inverse Head and Shoulders

        for ($i = 2; $i < count($lows); $i++) {

            $leftShoulder = $lows[$i - 2];

            $head = $lows[$i - 1];

            $rightShoulder = $lows[$i];



            // Ensure valid indices and minimum distance

            if ($rightShoulder['index'] - $leftShoulder['index'] < $minDistance * 2) {

                continue;

            }



            if ($head['price'] < $leftShoulder['price'] && $head['price'] < $rightShoulder['price'] &&

                abs($leftShoulder['price'] - $rightShoulder['price']) / $leftShoulder['price'] < $threshold) {

                // Extract slice safely

                $slice = array_slice($ohlcData, $leftShoulder['index'], $rightShoulder['index'] - $leftShoulder['index']);

                if (empty($slice)) {

                    continue;

                }



                $neckline = max(array_column($slice, 'high'));

                $recentClose = $this->safeDecimalCast(end($ohlcData)['close']);



                if ($recentClose > $neckline) {

                    $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);

                    if ($recentVolume > $leftShoulder['volume'] && $recentVolume > $rightShoulder['volume']) {

                        return [

                            'pattern_name' => 'Inverse Head and Shoulders Pattern',

                            'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                            'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                            'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                            'pattern_category' => $patternConfig['category'],

                            'risk_level' => $patternConfig['risk_level']

                        ];

                    }

                }

            }

        }



        return null;

    }



    /**

     * Detect Gaps pattern

     */

    private function detectGaps(array $ohlcData, array $patternConfig): ?array

    {

        if (!$this->validateOhlcData($ohlcData, 10, 'Gaps Chart Patterns')) {

            return null;

        }



        $gapThreshold = 0.01; // 1% minimum gap size

        $minGaps = 1; // At least one significant gap recently



        $gaps = [];

        for ($i = 1; $i < count($ohlcData); $i++) {

            $prev = $ohlcData[$i - 1];

            $curr = $ohlcData[$i];



            $prevHigh = $this->safeDecimalCast($prev['high']);

            $prevLow = $this->safeDecimalCast($prev['low']);

            $currOpen = $this->safeDecimalCast($curr['open']);

            $currVolume = $this->safeDecimalCast($curr['volume']);

            $prevVolume = $this->safeDecimalCast($prev['volume']);



            $gapSizeUp = ($currOpen - $prevHigh) / $prevHigh;

            $gapSizeDown = ($prevLow - $currOpen) / $prevLow;



            if ($gapSizeUp > $gapThreshold) {

                $gaps[] = ['type' => 'up', 'size' => $gapSizeUp, 'index' => $i, 'volume' => $currVolume];

            } elseif ($gapSizeDown > $gapThreshold) {

                $gaps[] = ['type' => 'down', 'size' => $gapSizeDown, 'index' => $i, 'volume' => $currVolume];

            }

        }



        if (count($gaps) >= $minGaps) {

            $recentGap = end($gaps);

            $recentVolume = $this->safeDecimalCast(end($ohlcData)['volume']);



            // Confirmation: Gap with increased volume, and not filled immediately (check if close doesn't fill the gap)

            $currClose = $this->safeDecimalCast(end($ohlcData)['close']);

            if ($recentGap['type'] === 'up' && $currClose > $ohlcData[$recentGap['index'] - 1]['high'] && $recentVolume > $ohlcData[$recentGap['index'] - 1]['volume']) {

                return [

                    'pattern_name' => 'Gaps Chart Patterns',

                    'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                    'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                    'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                    'pattern_category' => $patternConfig['category'],

                    'risk_level' => $patternConfig['risk_level']

                ];

            } elseif ($recentGap['type'] === 'down' && $currClose < $ohlcData[$recentGap['index'] - 1]['low'] && $recentVolume > $ohlcData[$recentGap['index'] - 1]['volume']) {

                return [

                    'pattern_name' => 'Gaps Chart Patterns',

                    'success_probability' => $this->safeDecimalCast($patternConfig['reliability']),

                    'avg_return' => $this->safeDecimalCast($patternConfig['avg_return']),

                    'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                    'pattern_category' => $patternConfig['category'],

                    'risk_level' => $patternConfig['risk_level']

                ];

            }

        }



        return null;

    }



    /**

     * Updated detectPattern method to detect multiple patterns per timeframe

     */

    private function detectPattern(Stock $stock, string $timeframe): array

    {

        try {

            // Fetch historical data for all timeframes

            $allTimeframeData = $this->fetchHistoricalData($stock);

            if (empty($allTimeframeData) || !isset($allTimeframeData[$timeframe]) || !$allTimeframeData[$timeframe]['success']) {

                Log::warning("No valid data for {$stock->symbol} on {$timeframe}");

                return [];

            }



            // Get data for the specific timeframe

            $ohlcData = $allTimeframeData[$timeframe]['data'];

            if (empty($ohlcData)) {

                Log::warning("Empty data for {$stock->symbol} on {$timeframe}");

                return [];

            }



            // Calculate max data points based on 12 years

            $maxDataPoints = match($timeframe) {

                'MONTHLY' => 12 * 12,         // 12 years * 12 months = 144

                'WEEKLY' => 12 * 52,          // 12 years * 52 weeks = 624

                'DAILY' => 12 * 252,          // 12 years * 252 trading days ≈ 3024

                'HOURLY' => 12 * 252 * 6,     // 12 years * 252 trading days * 6 hours/day ≈ 18144

                '15MIN' => 12 * 252 * 24,     // 12 years * 252 * (6.5 hours * 4 intervals/hour) ≈ 72576

                '5MIN' => 12 * 252 * 78,      // 12 years * 252 * (6.5 hours * 12 intervals/hour) ≈ 235872

                default => count($ohlcData),

            };

            

            $ohlcData = array_slice($ohlcData, -$maxDataPoints);

            

            // Define pattern detectors

            $patternDetectors = [

                'Double Bottom Chart Pattern' => fn($data) => $this->detectDoubleBottom($data, $this->patternTypes['Double Bottom Chart Pattern']),

                'Head and Shoulders Chart Pattern' => fn($data) => $this->detectHeadAndShoulders($data, $this->patternTypes['Head and Shoulders Chart Pattern']),

                'Cup and Handle Chart Pattern' => fn($data) => $this->detectCupAndHandle($data, $this->patternTypes['Cup and Handle Chart Pattern']),

                'Ascending Triangle Chart Pattern' => fn($data) => $this->detectAscendingTriangle($data, $this->patternTypes['Ascending Triangle Chart Pattern']),

                'Descending Triangle Chart Pattern' => fn($data) => $this->detectDescendingTriangle($data, $this->patternTypes['Descending Triangle Chart Pattern']),

                'Symmetrical Triangle Chart Pattern' => fn($data) => $this->detectSymmetricalTriangle($data, $this->patternTypes['Symmetrical Triangle Chart Pattern']),

                'Pennant Chart Pattern' => fn($data) => $this->detectPennant($data, $this->patternTypes['Pennant Chart Pattern']),

                'Bullish Flag Chart Pattern' => fn($data) => $this->detectBullishFlag($data, $this->patternTypes['Bullish Flag Chart Pattern']),

                'Bearish Flag Chart Pattern' => fn($data) => $this->detectBearishFlag($data, $this->patternTypes['Bearish Flag Chart Pattern']),

                'Rising Wedge Chart Pattern' => fn($data) => $this->detectRisingWedge($data, $this->patternTypes['Rising Wedge Chart Pattern']),

                'Falling Wedge Chart Pattern' => fn($data) => $this->detectFallingWedge($data, $this->patternTypes['Falling Wedge Chart Pattern']),

                'Double Top Chart Pattern' => fn($data) => $this->detectDoubleTop($data, $this->patternTypes['Double Top Chart Pattern']),

                'Inverse Head and Shoulders Pattern' => fn($data) => $this->detectInverseHeadAndShoulders($data, $this->patternTypes['Inverse Head and Shoulders Pattern']),

                'Triple Top Chart Patterns' => fn($data) => $this->detectTripleTop($data, $this->patternTypes['Triple Top Chart Patterns']),

                'Triple Bottom Chart Pattern' => fn($data) => $this->detectTripleBottom($data, $this->patternTypes['Triple Bottom Chart Pattern']),

                'Gaps Chart Patterns' => fn($data) => $this->detectGaps($data, $this->patternTypes['Gaps Chart Patterns']),

                // Remaining use generic (improved)

                'Rounding Top Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Rounding Top Chart Pattern'], 'Rounding Top Chart Pattern'),

                'Rounding Bottom Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Rounding Bottom Chart Pattern'], 'Rounding Bottom Chart Pattern'),

                'Bump and Run Chart Patterns' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Bump and Run Chart Patterns'], 'Bump and Run Chart Patterns'),

                'Price Channel Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Price Channel Chart Pattern'], 'Price Channel Chart Pattern'),

                'Diamond Top Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Diamond Top Chart Pattern'], 'Diamond Top Chart Pattern'),

                'Diamond Bottom Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Diamond Bottom Chart Pattern'], 'Diamond Bottom Chart Pattern'),

                'Channel Chart Patterns' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Channel Chart Patterns'], 'Channel Chart Patterns'),

                'Bullish Rectangle Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Bullish Rectangle Chart Pattern'], 'Bullish Rectangle Chart Pattern'),

                'Bearish Rectangle Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Bearish Rectangle Chart Pattern'], 'Bearish Rectangle Chart Pattern'),

                'Pipe Top Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Pipe Top Chart Pattern'], 'Pipe Top Chart Pattern'),

                'Pipe Bottom Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Pipe Bottom Chart Pattern'], 'Pipe Bottom Chart Pattern'),

                'Spikes Stock Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Spikes Stock Chart Pattern'], 'Spikes Stock Chart Pattern'),

                'Ascending Staircase Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Ascending Staircase Chart Pattern'], 'Ascending Staircase Chart Pattern'),

                'Descending Staircase Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Descending Staircase Chart Pattern'], 'Descending Staircase Chart Pattern'),

                'Megaphone Stock Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Megaphone Stock Chart Pattern'], 'Megaphone Stock Chart Pattern'),

                'V Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['V Chart Pattern'], 'V Chart Pattern'),

                'Harmonic Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Harmonic Chart Pattern'], 'Harmonic Chart Pattern'),

                'Elliott Wave Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Elliott Wave Chart Pattern'], 'Elliott Wave Chart Pattern'),

                'Three Drives Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Three Drives Chart Pattern'], 'Three Drives Chart Pattern'),

                'Quasimodo Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Quasimodo Chart Pattern'], 'Quasimodo Chart Pattern'),

                'Dead Cat Bounce Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Dead Cat Bounce Chart Pattern'], 'Dead Cat Bounce Chart Pattern'),

                'Island Reversal Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Island Reversal Chart Pattern'], 'Island Reversal Chart Pattern'),

                'Tower Top Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Tower Top Chart Pattern'], 'Tower Top Chart Pattern'),

                'Tower Bottom Chart Patterns' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Tower Bottom Chart Patterns'], 'Tower Bottom Chart Patterns'),

                'Shakeout Chart Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Shakeout Chart Pattern'], 'Shakeout Chart Pattern'),

                'Broadening Wedge Pattern (Expanding Triangle)' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Broadening Wedge Pattern (Expanding Triangle)'], 'Broadening Wedge Pattern (Expanding Triangle)'),

                'Parabolic Curve Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Parabolic Curve Pattern'], 'Parabolic Curve Pattern'),

                'Bullish Wolfe Wave' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Bullish Wolfe Wave'], 'Bullish Wolfe Wave'),

                'Bearish Wolfe Wave Pattern' => fn($data) => $this->detectGenericPattern($data, $this->patternTypes['Bearish Wolfe Wave Pattern'], 'Bearish Wolfe Wave Pattern')

            ];



            $detectedBasicPatterns = [];

            foreach ($patternDetectors as $patternName => $detector) {

                $basicPattern = $detector($ohlcData);

                if ($basicPattern) {

                    $detectedBasicPatterns[$patternName] = $basicPattern;

                }

            }



            $detectedPatterns = [];

            foreach ($detectedBasicPatterns as $patternName => $basicPattern) {

                $patternConfig = $this->patternTypes[$patternName];

                

                // Calculate additional attributes

                $rank = $this->calculatePatternRank($stock, $timeframe, $patternConfig);

                $successProbability = $this->safeDecimalCast($basicPattern['success_probability']);

                $marketAdjustment = $this->getEnhancedMarketAdjustment($stock, $timeframe);

                $volumeAdjustment = $this->getVolumeAdjustment($stock);

                $finalSuccess = max(60, min(98, $successProbability + $marketAdjustment + $volumeAdjustment));

                $reliability = $this->getReliabilityGrade($finalSuccess);

                $avgReturn = $this->safeDecimalCast($basicPattern['avg_return']);

                $finalReturn = max(3, min(35, $avgReturn + rand(-2, 6)));



                $detectedPatterns[] = [

                    'timeframe' => $timeframe,

                    'pattern_name' => $patternName,

                    'rank' => $this->safeIntegerCast($rank),

                    'success_probability' => $this->safeDecimalCast(round($finalSuccess, 2)),

                    'avg_return' => $this->safeDecimalCast(round($finalReturn, 2)),

                    'reliability' => $reliability,

                    'risk_reward' => $this->safeDecimalCast($this->calculateRiskReward($finalReturn, $patternConfig['risk_level'])),

                    'risk_level' => $patternConfig['risk_level'],

                    'bullish_probability' => $this->safeDecimalCast($patternConfig['bullish_probability']),

                    'pattern_category' => $patternConfig['category'],

                    'detected_at' => now()

                ];

            }



            if (empty($detectedPatterns)) {

                Log::info("No patterns detected for {$stock->symbol} on {$timeframe}");

            }



            return $detectedPatterns;

        } catch (Exception $e) {

            Log::error("Pattern detection failed for {$stock->symbol} on {$timeframe}: " . $e->getMessage());

            return [];

        }

    }



    // Method to classify patterns (call this in the constructor or as needed)

    private function classifyPatterns(): void

    {

        foreach ($this->patternTypes as $name => $config) {

            $prob = $config['bullish_probability'] ?? 50;

            if ($prob > 50) {

                $this->bullishPatterns[$name] = $config;

            } elseif ($prob < 50) {

                $this->bearishPatterns[$name] = $config;

            } else {

                $this->neutralPatterns[$name] = $config;

            }

        }

    }



    // Getter methods

    public function getBullishPatterns(): array

    {

        return $this->bullishPatterns;

    }



    public function getBearishPatterns(): array

    {

        return $this->bearishPatterns;

    }



    public function getNeutralPatterns(): array

    {

        return $this->neutralPatterns;

    }

}