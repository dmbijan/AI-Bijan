<?php

namespace App\Documents\Templates;

class SuratTunjukSebab extends Surat
{
    public function key(): string
    {
        return 'surat_tunjuk_sebab';
    }

    public function prefix(): string
    {
        return 'STS';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Surat Tunjuk Sebab', 'Show Cause Letter');
    }

    protected function subject(array $d, string $l): string
    {
        return $this->t($l, 'Surat tunjuk sebab: ', 'Show cause: ').$d['matter'] ?? '';
    }

    protected function extraRules(): array
    {
        return ['matter' => 'required|string|max:150', 'allegations' => 'required|array|min:1', 'allegations.*' => 'string|max:1000', 'reply_days' => 'nullable|integer|min:1|max:30'];
    }

    protected function body(array $d, string $l): array
    {
        return $l === 'en' ? [
            'The company has received information regarding '.$d['matter'].'. The particulars of the allegation(s) are: '.implode('; ', $d['allegations']).'.',
            'You are therefore required to explain in writing and show cause why disciplinary action should not be taken against you.',
            'Your explanation must be submitted within '.($d['reply_days'] ?? 7).' days of the date of this letter. Failing which, it may be deemed that you have no explanation and the company may proceed on the facts available.',
            'This letter gives you the opportunity to be heard in line with the principles of natural justice; it is not a decision.',
        ] : [
            'Pihak syarikat telah menerima maklumat berhubung '.$d['matter'].'. Butiran pertuduhan/dakwaan adalah seperti berikut: '.implode('; ', $d['allegations']).'.',
            'Sehubungan itu, anda dikehendaki memberikan penjelasan bertulis dan menunjukkan sebab mengapa tindakan tatatertib tidak boleh diambil terhadap anda.',
            'Penjelasan hendaklah diserahkan dalam tempoh '.($d['reply_days'] ?? 7).' hari dari tarikh surat ini. Kegagalan berbuat demikian boleh dianggap bahawa anda tiada penjelasan, dan syarikat boleh meneruskan tindakan berdasarkan fakta yang ada.',
            'Surat ini dikeluarkan bagi memberi anda peluang untuk didengar selaras dengan prinsip keadilan asasi; ia bukan suatu keputusan.',
        ];
    }

    protected function acknowledgement(array $d, string $l): ?string
    {
        return $this->t($l, 'Saya, '.$d['recipient']['name'].', mengaku telah menerima surat ini.', 'I, '.$d['recipient']['name'].', acknowledge receipt of this letter.');
    }
}
