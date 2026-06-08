<?php

namespace App\Console\Commands;

use App\Models\Number;
use App\Services\PredictionService;
use Illuminate\Console\Command;

class GeneratePredictionSnapshots extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:generate-prediction-snapshots {date?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate pre-calculated prediction snapshots for better performance';

  /**
   * Execute the console command.
   */
  public function handle(PredictionService $predictionService)
  {
    $date = $this->argument('date') ?: now()->toDateString();
    $this->info("Generating snapshots for date: $date");

    $regions = [Number::REGION_MB]; // Có thể thêm các vùng khác nếu cần

    foreach ($regions as $region) {
      $this->info("Processing region: $region");

      // Generate for FREE users
      $this->info("- Generating FREE snapshot...");
      $predictionService->generateSnapshot($region, $date, false);

      // Generate for VIP users
      $this->info("- Generating VIP snapshot...");
      $predictionService->generateSnapshot($region, $date, true, true);
    }

    $this->info("All snapshots generated successfully!");
  }
}
