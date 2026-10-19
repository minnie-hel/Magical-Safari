<?php
declare(strict_types=1);

const OFFICE_EMAIL = "info@magicaleastafricaheritagesafaris.com";

function book_clean(mixed $value, int $max): string
{
    $text = trim(str_replace(["\r", "\n"], " ", (string) $value));
    if (function_exists("mb_substr")) {
        return mb_substr($text, 0, $max);
    }
    return substr($text, 0, $max);
}

function book_reply(int $status, array $body): void
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($body);
}

function book_handle(array $data, bool $deliver = true): array
{
    if (book_clean($data["mehs_leave_blank"] ?? "", 200) !== "") {
        return [200, ["ok" => true]];
    }

    $name = book_clean($data["name"] ?? "", 120);
    $email = book_clean($data["email"] ?? "", 160);
    $phone = book_clean($data["phone"] ?? "", 40);
    $whatsapp = book_clean($data["whatsapp"] ?? "", 40);
    $country = book_clean($data["country"] ?? "", 80);
    $date = book_clean($data["date"] ?? "", 40);
    $month = book_clean($data["month"] ?? "", 40);
    $people = book_clean($data["adults"] ?? "", 10);
    $children = book_clean($data["children"] ?? "", 10);
    $budget = book_clean($data["budget"] ?? "", 40);
    $trip = book_clean($data["trip"] ?? "", 180);
    $total = book_clean($data["total"] ?? "", 40);
    $note = book_clean($data["note"] ?? "", 2000);
    $interests = $data["interest"] ?? [];
    if (!is_array($interests)) {
        $interests = [$interests];
    }
    $interest = book_clean(implode(", ", array_map(fn($item) => book_clean($item, 40), $interests)), 240);

    if ($name === "" || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return [400, ["ok" => false, "error" => "Add your name and a valid email address."]];
    }

    $rows = [
        "Name" => $name,
        "Email" => $email,
        "Phone" => $phone,
        "WhatsApp" => $whatsapp,
        "Country" => $country,
        "Travel date" => $date,
        "Travel month" => $month,
        "People" => $people,
        "Children" => $children,
        "Budget" => $budget,
        "Interests" => $interest,
        "Trip" => $trip !== "" ? $trip : "Not chosen yet",
        "Estimated total" => $total,
        "Note" => $note,
    ];
    $lines = ["Booking for Magical East Africa Heritage Safaris", ""];
    foreach ($rows as $label => $value) {
        if ($value === "" || ($label === "Children" && $value === "0")) {
            continue;
        }
        $lines[] = $label . ": " . $value;
    }
    $message = implode("\n", $lines);
    $subject = "Safari booking from " . $name;
    $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    $headers = implode("\r\n", [
        "MIME-Version: 1.0",
        "Content-Type: text/plain; charset=UTF-8",
        "From: Magical East Africa Heritage Safaris <" . OFFICE_EMAIL . ">",
        "Reply-To: " . $name . " <" . $email . ">",
    ]);
    if (!$deliver) {
        return [200, ["ok" => true, "preview" => $message]];
    }
    $sent = mail(OFFICE_EMAIL, $encodedSubject, $message, $headers, "-f" . OFFICE_EMAIL);
    if (!$sent) {
        return [502, ["ok" => false, "error" => "The office email could not be sent."]];
    }
    return [200, ["ok" => true]];
}

if (PHP_SAPI === "cli") {
    $sample = book_handle([
        "name" => "Amina Kariuki",
        "email" => "amina@example.com",
        "phone" => "+254700000000",
        "adults" => "2",
        "children" => "0",
        "trip" => "5 days Amboseli",
        "note" => "April",
    ], false);
    $missing = book_handle(["name" => "Amina", "email" => "not-an-email"], false);
    $trap = book_handle(["mehs_leave_blank" => "bot", "name" => "", "email" => ""], false);
    if ($sample[0] !== 200 || !str_contains((string) ($sample[1]["preview"] ?? ""), "Trip: 5 days Amboseli")) {
        fwrite(STDERR, "sample status {$sample[0]}\n");
        exit(1);
    }
    if (str_contains((string) ($sample[1]["preview"] ?? ""), "Children:")) {
        fwrite(STDERR, "zero children should be omitted\n");
        exit(1);
    }
    if ($missing[0] !== 400 || $trap[0] !== 200 || ($trap[1]["ok"] ?? false) !== true) {
        fwrite(STDERR, "validation failed\n");
        exit(1);
    }
    fwrite(STDOUT, "book.php checks passed\n");
    exit(0);
}

if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
    book_reply(405, ["ok" => false, "error" => "Use the booking form."]);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw === false ? "" : $raw, true);
if (!is_array($data)) {
    $data = $_POST;
}
[$status, $body] = book_handle($data);
book_reply($status, $body);
