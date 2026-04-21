<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeightOptimizer;
use App\Models\NumberStat;
use App\Models\Result;
use App\Models\Number;
use Carbon\Carbon;

class OptimizeWeightsCommand extends Command
{
    protected $signature = 'optimize:weights {--days=90} {--top=5}';
    protected $description = 'Auto tune weights using historical data';

    public function handle()
    {
        $days = (int) $this->option('days');
        $topN = (int) $this->option('top');

        $this->info("🔍 Building dataset {$days} days...");

        $dataset = $this->buildDataset($days);

        $this->info("⚙️ Generating candidates...");
        $candidates = $this->generateCandidates();
        $this->output->progressStart(count($candidates));

        $this->info("🚀 Optimizing...");
        $result = app(WeightOptimizer::class)
            ->optimize($candidates, $dataset, $topN);

        $this->info("✅ Done!");

        $this->line(json_encode($result, JSON_PRETTY_PRINT));
    }

    // =========================
    // DATASET
    // =========================

    protected function buildDataset(int $days)
    {
        $data = collect();

        $dates = Result::orderByDesc('date')
            ->limit($days + 1)
            ->pluck('date')
            ->unique()
            ->values();

        for ($i = 0; $i < count($dates) - 1; $i++) {
            $date = $dates[$i];
            $nextDate = $dates[$i + 1];

            $stats = NumberStat::whereDate('updated_at', '<=', $date)->get();

            $resultNumbers = Number::whereHas('result', function ($q) use ($nextDate) {
                $q->where('date', $nextDate);
            })
                ->whereNotIn('prize', ['ma_db', 'db'])
                ->pluck('number')
                ->toArray();

            if (empty($resultNumbers)) continue;

            $data->push([
                'stats' => $stats,
                'result' => $resultNumbers,
            ]);
        }

        return $data;
    }

    // =========================
    // CANDIDATES
    // =========================

    protected function generateCandidates(): array
    {
        $candidates = [];

        foreach ([0.2, 0.3, 0.4] as $gap) {
            foreach ([0.1, 0.2] as $trend) {
                foreach ([0.1, 0.15, 0.2] as $mr) {
                    foreach ([0.1, 0.2] as $cooldown) {

                        $weights = [
                            'gap' => $gap,
                            'db_gap' => 0.2,
                            'freq' => 0.1,
                            'cooldown' => $cooldown,
                            'trend' => $trend,
                            'mean_reversion' => $mr,
                        ];

                        // normalize tổng = 1 (optional nhưng nên)
                        $sum = array_sum($weights);
                        foreach ($weights as $k => $v) {
                            $weights[$k] = $v / $sum;
                        }

                        $candidates[] = $weights;
                    }
                }
            }
        }

        return $candidates;
    }
}
