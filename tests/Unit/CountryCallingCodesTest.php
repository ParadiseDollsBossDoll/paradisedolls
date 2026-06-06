<?php

namespace Tests\Unit;

use App\Support\CountryCallingCodes;
use Tests\TestCase;

class CountryCallingCodesTest extends TestCase
{
    public function test_phone_options_include_country_specific_nanp_prefixes(): void
    {
        $options = collect(CountryCallingCodes::phoneOptions(config('country_calling_codes')))
            ->keyBy('value');

        $this->assertSame('+1 264', $options->get('AI')['code']);
        $this->assertSame('+1264', $options->get('AI')['prefix']);
        $this->assertSame('+1 684', $options->get('AS')['code']);
        $this->assertSame('+1 721', $options->get('SX')['code']);
    }

    public function test_phone_options_expand_countries_with_multiple_valid_prefixes(): void
    {
        $options = collect(CountryCallingCodes::phoneOptions(config('country_calling_codes')))
            ->keyBy('value');

        $this->assertSame('+1 809', $options->get('DO-809')['code']);
        $this->assertSame('+1829', $options->get('DO-829')['prefix']);
        $this->assertSame('+1 658', $options->get('JM-658')['code']);
        $this->assertSame('+1939', $options->get('PR-939')['prefix']);
    }

    public function test_split_phone_matches_longest_country_specific_prefix_first(): void
    {
        $split = CountryCallingCodes::splitPhone('+12645551234', 'Anguilla', config('country_calling_codes'));

        $this->assertSame('AI', $split['country']);
        $this->assertSame('5551234', $split['number']);

        $split = CountryCallingCodes::splitPhone('+18295551234', 'Dominican Republic', config('country_calling_codes'));

        $this->assertSame('DO-829', $split['country']);
        $this->assertSame('5551234', $split['number']);
    }

    public function test_split_phone_prefers_default_country_for_shared_base_codes(): void
    {
        $split = CountryCallingCodes::splitPhone('+447700900123', null, config('country_calling_codes'));

        $this->assertSame('GB', $split['country']);
        $this->assertSame('7700900123', $split['number']);
    }
}
