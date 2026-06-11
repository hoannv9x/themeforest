<?php

namespace Database\Seeders;

use App\Models\Vocabulary;
use App\Models\VocabularyExample;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VocabularySeeder extends Seeder
{
    public function run(): void
    {
        $vocabularies = [
            // Beginner (A1-A2)
            ['word' => 'Apple', 'ipa' => '/ˈæp.əl/', 'meaning' => 'Quả táo', 'difficulty' => 'beginner'],
            ['word' => 'Book', 'ipa' => '/bʊk/', 'meaning' => 'Quyển sách', 'difficulty' => 'beginner'],
            ['word' => 'Cat', 'ipa' => '/kæt/', 'meaning' => 'Con mèo', 'difficulty' => 'beginner'],
            ['word' => 'Dog', 'ipa' => '/dɒɡ/', 'meaning' => 'Con chó', 'difficulty' => 'beginner'],
            ['word' => 'Eat', 'ipa' => '/iːt/', 'meaning' => 'Ăn', 'difficulty' => 'beginner'],
            ['word' => 'Friend', 'ipa' => '/frend/', 'meaning' => 'Người bạn', 'difficulty' => 'beginner'],
            ['word' => 'Go', 'ipa' => '/ɡəʊ/', 'meaning' => 'Đi', 'difficulty' => 'beginner'],
            ['word' => 'Happy', 'ipa' => '/ˈhæp.i/', 'meaning' => 'Hạnh phúc', 'difficulty' => 'beginner'],
            ['word' => 'Ice', 'ipa' => '/aɪs/', 'meaning' => 'Băng, đá', 'difficulty' => 'beginner'],
            ['word' => 'Job', 'ipa' => '/dʒɒb/', 'meaning' => 'Công việc', 'difficulty' => 'beginner'],
            // ... (Tiếp tục với khoảng 50 từ thực tế đa dạng)
            ['word' => 'Beautiful', 'ipa' => '/ˈbjuː.tɪ.fəl/', 'meaning' => 'Đẹp', 'difficulty' => 'beginner'],
            ['word' => 'Family', 'ipa' => '/ˈfæm.əl.i/', 'meaning' => 'Gia đình', 'difficulty' => 'beginner'],
            ['word' => 'School', 'ipa' => '/skuːl/', 'meaning' => 'Trường học', 'difficulty' => 'beginner'],
            ['word' => 'Water', 'ipa' => '/ˈwɔː.tər/', 'meaning' => 'Nước', 'difficulty' => 'beginner'],
            ['word' => 'Time', 'ipa' => '/taɪm/', 'meaning' => 'Thời gian', 'difficulty' => 'beginner'],

            // Intermediate (B1-B2)
            ['word' => 'Aesthetic', 'ipa' => '/esˈθet.ɪk/', 'meaning' => 'Thẩm mỹ', 'difficulty' => 'intermediate'],
            ['word' => 'Benevolent', 'ipa' => '/bəˈnev.əl.ənt/', 'meaning' => 'Nhân từ', 'difficulty' => 'intermediate'],
            ['word' => 'Challenge', 'ipa' => '/ˈtʃæl.ɪndʒ/', 'meaning' => 'Thử thách', 'difficulty' => 'intermediate'],
            ['word' => 'Diverse', 'ipa' => '/daɪˈvɜːs/', 'meaning' => 'Đa dạng', 'difficulty' => 'intermediate'],
            ['word' => 'Efficient', 'ipa' => '/ɪˈfɪʃ.ənt/', 'meaning' => 'Hiệu quả', 'difficulty' => 'intermediate'],
            ['word' => 'Flexible', 'ipa' => '/ˈflek.sə.bəl/', 'meaning' => 'Linh hoạt', 'difficulty' => 'intermediate'],
            ['word' => 'Genuine', 'ipa' => '/ˈdʒen.ju.ɪn/', 'meaning' => 'Chân thật', 'difficulty' => 'intermediate'],
            ['word' => 'Hesitate', 'ipa' => '/ˈhez.ɪ.teɪt/', 'meaning' => 'Do dự', 'difficulty' => 'intermediate'],
            ['word' => 'Influence', 'ipa' => '/ˈɪn.flu.əns/', 'meaning' => 'Ảnh hưởng', 'difficulty' => 'intermediate'],
            ['word' => 'Justify', 'ipa' => '/ˈdʒʌs.tɪ.faɪ/', 'meaning' => 'Bào chữa, thanh minh', 'difficulty' => 'intermediate'],

            // Advanced (C1-C2)
            ['word' => 'Ambiguous', 'ipa' => '/æmˈbɪɡ.ju.əs/', 'meaning' => 'Mơ hồ, nhập nhằng', 'difficulty' => 'advanced'],
            ['word' => 'Capricious', 'ipa' => '/kəˈprɪʃ.əs/', 'meaning' => 'Thất thường', 'difficulty' => 'advanced'],
            ['word' => 'Delineate', 'ipa' => '/dɪˈlɪn.i.eɪt/', 'meaning' => 'Phác họa, mô tả chi tiết', 'difficulty' => 'advanced'],
            ['word' => 'Ephemeral', 'ipa' => '/ɪˈfem.ər.əl/', 'meaning' => 'Phù du, chóng tàn', 'difficulty' => 'advanced'],
            ['word' => 'Fastidious', 'ipa' => '/fæsˈtɪd.i.əs/', 'meaning' => 'Khó tính, kén chọn', 'difficulty' => 'advanced'],
            ['word' => 'Garrulous', 'ipa' => '/ˈɡær.əl.əs/', 'meaning' => 'Ba hoa, lắm lời', 'difficulty' => 'advanced'],
            ['word' => 'Heuristic', 'ipa' => '/hjʊˈrɪs.tɪk/', 'meaning' => 'Khám phá, tìm tòi', 'difficulty' => 'advanced'],
            ['word' => 'Ineffable', 'ipa' => '/ɪnˈef.ə.bəl/', 'meaning' => 'Không thốt nên lời', 'difficulty' => 'advanced'],
            ['word' => 'Juxtapose', 'ipa' => '/ˌdʒʌk.stəˈpəʊz/', 'meaning' => 'Để cạnh nhau', 'difficulty' => 'advanced'],
            ['word' => 'Loquacious', 'ipa' => '/ləˈkweɪ.ʃəs/', 'meaning' => 'Nói nhiều', 'difficulty' => 'advanced'],
        ];

        foreach ($vocabularies as $v) {
            $v['slug'] = Str::slug($v['word']);
            $vocabulary = Vocabulary::create($v);

            VocabularyExample::create([
                'vocabulary_id' => $vocabulary->id,
                'example_sentence' => "This is a sample sentence for the word: " . $v['word'],
                'translation' => "Đây là câu ví dụ cho từ: " . $v['word']
            ]);
        }

        // Tạo thêm khoảng 500 từ ngẫu nhiên để đạt số lượng yêu cầu
        Vocabulary::factory()->count(500)->create()->each(function ($v) {
            VocabularyExample::factory()->count(fake()->numberBetween(1, 2))->create([
                'vocabulary_id' => $v->id
            ]);
        });
    }
}
