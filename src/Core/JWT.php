<?php

// JSON Web Token (Task 17) - written by hand so you can see how it works.
//
// A token has 3 parts joined by dots:   HEADER.PAYLOAD.SIGNATURE
//   header    = {"alg":"HS256","typ":"JWT"}
//   payload   = {"sub":2,"username":"user","role":"user","exp":1700000000}
//   signature = HMAC-SHA256(header.payload, secret)
//
// Anyone can READ the payload (it is only base64), but nobody can CHANGE it
// without the secret, because the signature would no longer match.
class JWT
{
    // make a token
    public static function encode(array $payload, string $secret): string
    {
        $header  = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = self::base64UrlEncode(json_encode($payload));
        $signature = self::base64UrlEncode(hash_hmac('sha256', "$header.$payload", $secret, true));

        return "$header.$payload.$signature";
    }

    // check a token: returns the payload, or null if it is fake, changed or expired
    public static function decode(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$header, $payload, $signature] = $parts;

        // only accept HS256 (blocks the "alg: none" trick)
        $headerData = json_decode(self::base64UrlDecode($header), true);
        if (($headerData['alg'] ?? '') !== 'HS256') {
            return null;
        }

        // the signature must match -> the token was made by us and not changed
        $expected = self::base64UrlEncode(hash_hmac('sha256', "$header.$payload", $secret, true));
        if (!hash_equals($expected, $signature)) { // hash_equals = safe compare (no timing attack)
            return null;
        }

        // not expired
        $data = json_decode(self::base64UrlDecode($payload), true);
        if (!is_array($data) || ($data['exp'] ?? 0) < time()) {
            return null;
        }

        return $data;
    }

    // base64 that is safe in URLs: + -> -, / -> _, no = at the end
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/'));
    }
}
