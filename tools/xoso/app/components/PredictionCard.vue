<template>
  <div
    :class="[
      data?.is_vip 
        ? 'bg-gradient-to-r from-yellow-500 via-orange-500 to-red-500' 
        : 'bg-gradient-to-r from-red-500 via-red-400 to-orange-400',
      'p-6 rounded-xl text-white max-sm:p-3'
    ]"
  >
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold">
        {{ data?.is_vip ? `🔥 Gợi ý lô VIP (${data?.date_label || 'Hôm nay'})` : `Gợi ý lô (${data?.date_label || 'Hôm nay'})` }}
      </h2>
      <span v-if="data?.is_vip" class="bg-white text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">
        VIP
      </span>
    </div>
    
    <!-- Strategy Suggestions Section - VIP Only -->
    <div v-if="data?.is_vip && data?.strategy_suggestions?.length" class="mb-6 space-y-3">
      <h3 class="text-lg font-bold">📋 Gợi ý chiến lược</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div
          v-for="(suggestion, index) in data.strategy_suggestions"
          :key="index"
          class="bg-white/95 backdrop-blur-sm rounded-lg p-4 text-black"
          :class="{
            'border-2 border-green-500': suggestion.type === 'strong',
            'border-2 border-yellow-500': suggestion.type === 'normal' || suggestion.type === 'gap_alert',
            'border-2 border-gray-400': suggestion.type === 'weak',
          }"
        >
          <div class="flex items-center gap-2 mb-2">
            <span v-if="suggestion.type === 'strong'" class="text-green-600 text-lg">💰</span>
            <span v-else-if="suggestion.type === 'normal'" class="text-yellow-600 text-lg">📊</span>
            <span v-else-if="suggestion.type === 'weak'" class="text-gray-600 text-lg">🛑</span>
            <span v-else class="text-blue-600 text-lg">⚠️</span>
            <span class="font-bold">{{ suggestion.title }}</span>
          </div>
          <p class="text-sm text-gray-700">{{ suggestion.description }}</p>
          <div v-if="suggestion.numbers" class="mt-2 flex flex-wrap gap-1">
            <span
              v-for="num in suggestion.numbers"
              :key="num"
              class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-bold"
            >
              {{ num }}
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- check data -->
    <div v-if="!data || !data.predictions?.ranking?.numbers">Không có dữ liệu</div>

    <!-- render numbers -->
    <div
      v-else
      class="grid gap-3"
      :class="{
        'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 max-sm:flex max-sm:flex-col':
          data.predictions?.ranking?.numbers[0].label,
        'grid-cols-3 md:grid-cols-5': !data.predictions?.ranking?.numbers[0].label,
      }"
    >
      <div
        v-for="(item, index) in data.predictions?.ranking?.numbers || []"
        :key="index"
      class="px-4 py-3 rounded-lg font-bold text-center flex justify-center items-center flex-col transition-all hover:scale-105"
      :class="[
        item.is_hit ? 'bg-green-500 text-white' : 'bg-white text-black',
        { 'max-sm:col-span-2': index == 2 && data.predictions?.ranking?.numbers[0].label }
      ]"
      >
        <div class="flex items-center gap-2 mb-1">
          <div class="text-2xl font-extrabold tracking-wide">
            {{ item.number }}
          </div>
          <div v-if="item.confidence && data.predictions?.ranking?.numbers[0].label" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">
            {{ Math.round(item.confidence) }}%
          </div>
        </div>

        <div
          v-if="item.label"
          class="text-xs mt-1 text-gray-500 flex items-center justify-center gap-1"
        >
          {{ item.label }}
        </div>

        <div v-if="item.message" class="text-xs mt-2 text-gray-600 leading-snug">
          {{ item.message }}
        </div>

        <div v-if="data?.is_vip && item.signals" class="mt-2 text-left w-full bg-gray-50 rounded p-2 text-xs">
          <div class="font-semibold text-gray-700 mb-1">Tín hiệu:</div>
          <div v-if="item.signals.z_score" class="text-gray-600">Z-score: {{ item.signals.z_score }}</div>
          <div v-if="item.signals.trend" class="text-gray-600">Trend: {{ item.signals.trend }}</div>
          <div v-if="item.signals.current_gap" class="text-gray-600">Gan: {{ item.signals.current_gap }}/{{ item.signals.max_gap }}</div>
        </div>
      </div>
    </div>

    <div v-if="data?.is_vip && data?.predictions?.bach_thu?.numbers?.length" class="mt-6">
      <h3 class="text-xl font-bold mb-4">🎯 Bạch thủ lô</h3>
      <div class="flex flex-wrap gap-3">
        <div
          v-for="(item, index) in data.predictions.bach_thu.numbers"
          :key="`bt-${index}-${item.number}`"
          class="px-5 py-4 rounded-lg font-bold text-center flex justify-center items-center flex-col transition-all hover:scale-105"
          :class="item.is_hit ? 'bg-green-500 text-white' : 'bg-white text-black'"
        >
          <div class="text-3xl font-extrabold tracking-wide">
            {{ item.number }}
          </div>
          <div v-if="item.message" class="text-xs mt-2 text-gray-600 leading-snug">
            {{ item.message }}
          </div>
        </div>
      </div>
    </div>
    <hr class="my-4 border-white/30" />
    <h3 class="text-xl font-bold mb-4">
      {{ data?.is_vip ? `🎯 Gợi ý đề VIP (${data?.date_label || 'Hôm nay'})` : `Gợi ý đề (${data?.date_label || 'Hôm nay'})` }}
    </h3>
    <div v-if="!data || !data.predictions?.db_ranking?.numbers">Không có dữ liệu</div>

    <!-- render numbers -->
    <div
      v-else
      class="grid gap-3"
      :class="{
        'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 max-sm:flex max-sm:flex-col':
          data.predictions?.db_ranking?.numbers[0].label,
        'grid-cols-3 md:grid-cols-5': !data.predictions?.db_ranking?.numbers[0].label,
      }"
    >
      <div
        v-for="(item, index) in data?.predictions?.db_ranking?.numbers || []"
        :key="index"
      class="px-4 py-3 rounded-lg font-bold text-center flex justify-center items-center flex-col transition-all hover:scale-105"
      :class="[
        item.is_hit ? 'bg-green-500 text-white' : 'bg-white text-black',
        { 'max-sm:col-span-2': index == 2 && data.predictions?.db_ranking?.numbers[0].label }
      ]"
      >
        <div class="flex items-center gap-2 mb-1">
          <div class="text-2xl font-extrabold tracking-wide">
            {{ item.number }}
          </div>
          <div v-if="item.confidence && data.predictions?.db_ranking?.numbers[0].label" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-semibold">
            {{ Math.round(item.confidence) }}%
          </div>
        </div>

        <div
          v-if="item.label"
          class="text-xs mt-1 text-gray-500 flex items-center justify-center gap-1"
        >
          {{ item.label }}
        </div>

        <div v-if="item.message" class="text-xs mt-2 text-gray-600 leading-snug">
          {{ item.message }}
        </div>

        <div v-if="data?.is_vip && item.signals" class="mt-2 text-left w-full bg-gray-50 rounded p-2 text-xs">
          <div class="font-semibold text-gray-700 mb-1">Tín hiệu DB:</div>
          <div v-if="item.signals.z_score_db" class="text-gray-600">Z-score: {{ item.signals.z_score_db }}</div>
          <div v-if="item.signals.current_gap_db" class="text-gray-600">Gan DB: {{ item.signals.current_gap_db }}/{{ item.signals.max_gap_db }}</div>
        </div>
      </div>
    </div>

    <div v-if="data?.predictions?.three_cang?.numbers?.length" class="mt-6 space-y-3">
      <h3 class="text-xl font-bold">🎰 Gợi ý 3 càng</h3>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="(item, index) in data.predictions.three_cang.numbers"
          :key="`3c-${index}`"
          class="px-3 py-2 rounded-lg text-sm font-bold flex items-center gap-2"
          :class="item.is_hit ? 'bg-green-500 text-white' : 'bg-white text-black'"
        >
          {{ item.number }}
        </span>
      </div>
    </div>

    <div class="mt-6">
      <p class="font-semibold">Lưu ý</p>
      <p class="text-xs opacity-80 mt-1">Dữ liệu được phân tích từ lịch sử gần đây</p>
      <p class="text-xs opacity-80">
        Confidence thể hiện mức độ tin cậy dựa trên phân tích chu kỳ và tần suất.
      </p>
      <p class="text-xs opacity-80">Chỉ mang tính tham khảo</p>
    </div>
  </div>
</template>

<script setup>
defineProps(["data"]);
</script>
