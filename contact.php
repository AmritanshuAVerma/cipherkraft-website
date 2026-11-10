<?php
/**
 * CipherKraft contact form handler (Hostinger shared hosting, PHP mail()).
 *
 * Setup on Hostinger:
 *  1. Create a mailbox on the domain (e.g. noreply@suverma.com) in hPanel > Emails.
 *     Hostinger rejects mail() whose From: is not on a domain hosted in the account.
 *  2. Set FROM_ADDRESS below to that mailbox.
 */

const TO_ADDRESS   = 'cipherkraft@outlook.com';
const FROM_ADDRESS = 'noreply@suverma.com';
const BACK_URL     = './';

function back(string $query): void {
    header('Location: ' . BACK_URL . '?' . $query . '#contact', true, 303);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . BACK_URL, true, 303);
    exit;
}

// Honeypot: bots fill the hidden "website" field. Pretend success.
if (!empty($_POST['website'])) {
    back('sent=1');
}

// Simple per-session rate limit: one message per 60 seconds.
session_start();
if (isset($_SESSION['ck_last']) && time() - $_SESSION['ck_last'] < 60) {
    back('error=rate');
}

// Strip CR/LF from single-line fields to block header injection.
function line(string $key, int $max): string {
    $v = trim((string)($_POST[$key] ?? ''));
    $v = preg_replace('/[\r\n\t]+/', ' ', $v);
    return mb_substr($v, 0, $max);
}

$name    = line('name', 100);
$email   = line('email', 150);
$company = line('company', 150);
$service = line('service', 60);
$message = mb_substr(trim((string)($_POST['message'] ?? '')), 0, 5000);

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
$body  = "New enquiry from the CipherKraft website\n";
$body .= "----------------------------------------\n";
$body .= "Name:    {$name}\n";
$body .= "Email:   {$email}\n";
$body .= "Company: " . ($company !== '' ? $company : '-') . "\n";
$body .= "Service: {$service}\n";
$body .= "IP:      " . ($_SERVER['REMOTE_ADDR'] ?? '-') . "\n";
$body .= "Time:    " . gmdate('Y-m-d H:i') . " UTC\n\n";
$body .= $message . "\n";

$headers = [
    'From: CipherKraft Website <' . FROM_ADDRESS . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . PHP_VERSION,
];

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$ok = mail(TO_ADDRESS, $encodedSubject, $body, implode("\r\n", $headers), '-f' . FROM_ADDRESS);

if ($ok) {
    $_SESSION['ck_last'] = time();
    back('sent=1');
}
back('error=send');
