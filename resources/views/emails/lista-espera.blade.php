<x-mail::message>
# Nova inscrição na lista de espera

Alguém se cadastrou para receber desconto no lançamento do **{{ $plano === 'familia' ? 'Plano Família' : 'Plano Clube' }}**.

<x-mail::panel>
**Nome:** {{ $nomeInteressado }}
**E-mail:** {{ $emailInteressado }}
**Plano:** {{ $plano === 'familia' ? 'Plano Família 👨‍👩‍👧' : 'Plano Clube 🏊' }}
**Data:** {{ now()->format('d/m/Y H:i') }}
</x-mail::panel>

Acesse o sistema para ver todos os inscritos na lista de espera.

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
