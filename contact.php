<?php
/**
 * Suverma contact form handler (Hostinger, PHP mail()).
 *
 * Setup on Hostinger:
 *  1. Create a mailbox on the domain (e.g. noreply@suverma.com) in hPanel > Emails.
 *     Hostinger rejects mail() whose From: is not on a domain hosted in the account.
 *  2. Set FROM_ADDRESS below to that mailbox.
 */

declare(strict_types=1);

const TO_ADDRESS    = 'amritanshuverma1993@gmail.com';
const FROM_ADDRESS  = 'noreply@suverma.com';
const BACK_URL      = './';
const RATE_WINDOW   = 3600; // seconds
const RATE_MAX      = 5;    // messages per IP per window
const MIN_FILL_SECS = 3;    // humans take longer than this to fill the form

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header_remove('X-Powered-By');

function back(string $query): void {
    header('Location: ' . BACK_URL . '?' . $query . '#contact', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . BACK_URL, true, 303);
    exit;
}

// Reject oversized bodies early.
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 20000) {
    back('error=invalid');
}

// Same-origin check: the form must be posted from this site (blocks cross-site posts).
$host    = strtolower((string)preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));
$source  = (string)($_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? ''));
$srcHost = strtolower((string)parse_url($source, PHP_URL_HOST));
if ($host === '' || $srcHost === '' || !hash_equals($host, $srcHost)) {
    back('error=invalid');
}

// Honeypot: bots fill the hidden "website" field. Pretend success.
if (!empty($_POST['website'])) {
    back('sent=1');
}

// Timing trap: script.js stamps the page-load time; instant submissions are bots.
$ts = (int)($_POST['ts'] ?? 0);
if ($ts > 0 && (time() - $ts) < MIN_FILL_SECS) {
    back('sent=1');
}

// Per-IP rate limit, stored outside the web root in the system temp dir.
$ip     = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$rlFile = sys_get_temp_dir() . '/sv_contact_' . hash('sha256', $ip) . '.json';
$now    = time();
$hits   = [];
if (is_file($rlFile)) {
    $decoded = json_decode((string)@file_get_contents($rlFile), true);
    if (is_array($decoded)) {
        $hits = array_values(array_filter($decoded, fn($t) => is_int($t) && $now - $t < RATE_WINDOW));
    }
}
if (count($hits) >= RATE_MAX) {
    back('error=rate');
}

// Strip control characters from single-line fields (blocks mail header injection).
function line(string $key, int $max): string {
    $v = (string)($_POST[$key] ?? '');
    $v = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $v) ?? '';
    return mb_substr(trim($v), 0, $max);
}

$name    = line('name', 100);
$email   = line('email', 150);
$company = line('company', 150);
$service = line('service', 60);
$message = (string)($_POST['message'] ?? '');
$message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $message) ?? '';
$message = mb_substr(trim($message), 0, 5000);

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    back('error=invalid');
}

$allowed = [
    'Custom software development', 'Cloud & DevOps', 'Cybersecurity', 'Data & AI',
    'Support & maintenance', 'IT consulting', 'Something else',
];
if (!in_array($service, $allowed, true)) {
    $service = 'Something else';
}

$subject = 'Website enquiry: ' . $service . ' — ' . $name;
$body  = "New enquiry from the Suverma website\n";
$body .= "----------------------------------------\n";
$body .= "Name:    {$name}\n";
$body .= "Email:   {$email}\n";
$body .= "Company: " . ($company !== '' ? $company : '-') . "\n";
$body .= "Service: {$service}\n";
$body .= "IP:      {$ip}\n";
$body .= "Time:    " . gmdate('Y-m-d H:i') . " UTC\n\n";
$body .= $message . "\n";

$headers = [
    'From: Suverma Website <' . FROM_ADDRESS . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
];

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$ok = mail(TO_ADDRESS, $encodedSubject, $body, implode("\r\n", $headers), '-f' . FROM_ADDRESS);

if ($ok) {
    $hits[] = $now;
    @file_put_contents($rlFile, json_encode($hits), LOCK_EX);
    back('sent=1');
}
back('error=send');
