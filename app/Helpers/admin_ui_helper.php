<?php

function admin_number(int|float|string $value): string
{
    return strtr(number_format((float)$value), ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',','=>'٬']);
}

function admin_date(?string $value, bool $withTime = false): string
{
    if (!$value) return '—';
    try {
        $timezone = new DateTimeZone('Asia/Tehran');
        $date = new DateTimeImmutable($value, $timezone);
        $formatter = new IntlDateFormatter('fa_IR@calendar=persian', IntlDateFormatter::NONE, IntlDateFormatter::NONE, $timezone, IntlDateFormatter::TRADITIONAL, $withTime ? 'yyyy/MM/dd • HH:mm' : 'yyyy/MM/dd');
        return $formatter->format($date) ?: $value;
    } catch (Throwable) {
        return $value;
    }
}
