<?php

namespace App\Services;

class ExplainService
{
  public function explainDb($item): array
  {
    $gapRatio = $item->max_gap_db > 0
      ? $item->current_gap_db / $item->max_gap_db
      : 0;

    $z = 0;
    if ($item->std_gap_db > 0 && $item->avg_gap_db > 0) {
      $z = ($item->current_gap_db - $item->avg_gap_db) / $item->std_gap_db;
    }

    $signals = [
      'gap_ratio' => round($gapRatio, 2),
      'current_gap_db' => $item->current_gap_db,
      'max_gap_db' => $item->max_gap_db,
      'z_score_db' => round($z, 2),
    ];

    return [
      'number' => $item->number,
      'label' => $this->getLabel($gapRatio, $z, $item),
      'signals' => $signals,
      'message' => $this->buildMessage($item, $gapRatio, $z),
    ];
  }

  // ===== LABEL =====

  protected function getLabel($gapRatio, $z, $item): string
  {
    if ($item->current_gap_db === 0) {
      return '❌ Vừa nổ DB';
    }

    if ($z > 1.5 && $z < 2.5) {
      return '🔥 Chu kỳ đẹp (DB)';
    }

    if ($z > 1) {
      return '📈 Đang tiến tới DB';
    }

    if ($gapRatio > 0.8) {
      return '⚠️ Gần chạm đỉnh DB';
    }

    return '❄️ Chưa có tín hiệu mạnh';
  }

  // ===== MESSAGE =====

  protected function buildMessage($item, $gapRatio, $z): string
  {
    if ($item->current_gap_db === 0) {
      return "Vừa ra giải đặc biệt, xác suất lặp lại thấp.";
    }

    if ($z > 1.5 && $z < 2.5) {
      return "Gan DB đang lệch +{$z}σ so với trung bình → khả năng hồi cao.";
    }

    if ($z > 1) {
      return "Gan DB đang tăng, có dấu hiệu tiến gần chu kỳ.";
    }

    if ($gapRatio > 0.8) {
      return "Gan DB đã cao ({$item->current_gap_db} ngày), cần theo dõi.";
    }

    return "Chưa có tín hiệu rõ ràng cho DB.";
  }

  protected function getLotoLabel($gapRatio, $z, $trend, $item): string
  {
    if ($item->current_gap == 0) {
      return '❌ Vừa ra gần đây';
    }

    if ($z > 1.5 && $trend > 1.1) {
      return '🔥 Đang vào form mạnh';
    }

    if ($z > 1.5) {
      return '🎯 Có khả năng hồi';
    }

    if ($trend > 1.2) {
      return '📈 Đang tăng mạnh';
    }

    if ($gapRatio > 0.8) {
      return '⚠️ Gan cao';
    }

    if ($trend < 0.8) {
      return '❄️ Đang nguội';
    }

    return '⚖️ Trung bình';
  }

  protected function buildLotoMessage($item, $gapRatio, $z, $trend): string
  {
    if ($item->current_gap == 0) {
      return "Vừa xuất hiện gần đây → xác suất lặp thấp.";
    }

    if ($z > 1.5 && $trend > 1.1) {
      return "Gan cao + trend tăng → số đang vào form mạnh.";
    }

    if ($z > 1.5) {
      return "Gan lệch +{$z}σ → có xu hướng hồi về trung bình.";
    }

    if ($trend > 1.2) {
      return "Tần suất 7 ngày tăng mạnh → dấu hiệu hot.";
    }

    if ($gapRatio > 0.8) {
      return "Gan cao ({$item->current_gap} ngày) → cần theo dõi.";
    }

    if ($trend < 0.8) {
      return "Tần suất giảm → số đang nguội dần.";
    }

    return "Không có tín hiệu rõ ràng.";
  }

  public function calculateTrend($item): float
  {
    $short = $item->total_count_7_days;
    $mid = max($item->total_count_30_days / 4, 1);

    return $short / $mid;
  }

  public function explainLoto($item): array
  {
    $gapRatio = $item->max_gap > 0
      ? $item->current_gap / $item->max_gap
      : 0;

    $z = 0;
    if ($item->std_gap > 0 && $item->avg_gap > 0) {
      $z = ($item->current_gap - $item->avg_gap) / $item->std_gap;
    }

    $trend = $this->calculateTrend($item);

    return [
      'number' => $item->number,
      'label' => $this->getLotoLabel($gapRatio, $z, $trend, $item),
      'signals' => [
        'gap_ratio' => round($gapRatio, 2),
        'current_gap' => $item->current_gap,
        'max_gap' => $item->max_gap,
        'z_score' => round($z, 2),
        'trend' => round($trend, 2),
        'freq_7' => $item->total_count_7_days,
        'freq_30' => $item->total_count_30_days,
      ],
      'message' => $this->buildLotoMessage($item, $gapRatio, $z, $trend),
    ];
  }
}
