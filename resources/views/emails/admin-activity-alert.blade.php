<div style="font-family: Arial, sans-serif; color: #241f23; line-height: 1.6;">
    <p style="margin: 0 0 0.75rem; font-size: 0.78rem; letter-spacing: 0.16em; text-transform: uppercase; color: #c36b86;">{{ __('Paradise Dolls Admin Alert') }}</p>
    <h1 style="margin: 0 0 1rem; font-size: 1.6rem; color: #1b171b;">{{ $heading }}</h1>
    <p style="margin: 0 0 1.25rem;">{{ $body }}</p>

    @if ($details !== [])
        <table style="width: 100%; margin: 0 0 1.25rem; border-collapse: collapse;">
            @foreach ($details as $label => $value)
                @if (filled($value))
                    <tr>
                        <td style="padding: 0.45rem 0.65rem; border: 1px solid #f5d5de; background: #fff7fa; font-size: 0.76rem; letter-spacing: 0.08em; text-transform: uppercase; color: #a66a7d;">{{ $label }}</td>
                        <td style="padding: 0.45rem 0.65rem; border: 1px solid #f5d5de; color: #241f23;">{{ $value }}</td>
                    </tr>
                @endif
            @endforeach
        </table>
    @endif

    <p style="margin: 0;">
        <a href="{{ $actionUrl }}" style="display: inline-block; background-color: #c36b86; color: #ffffff; text-decoration: none; padding: 0.7rem 1.15rem; font-weight: 700;">
            {{ $actionLabel }}
        </a>
    </p>
</div>
