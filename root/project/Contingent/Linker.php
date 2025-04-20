<?php
namespace Contingent;
use Utopia\KoSu;
use \PDO;

class Linker {

    public static function replaceHashtagsWithLinks($text) {
        // Regular expression to find hashtags
        $pattern = '/#(\w+)/';

        // Replace hashtags with anchor tags
        $replacement = '<a href="/tag/$1/1">#$1</a>';

        // Perform the replacement
        $text = preg_replace($pattern, $replacement, $text);

        return $text;
    }

    public static function replaceUsernamesWithLinks($text) {
        // Regular expression to find usernames
        $pattern = '/@(\w+)/';

        // Perform the replacement with a callback function
        $text = preg_replace_callback($pattern, [self::class, 'replaceUsernameCallback'], $text);

        return $text;
    }

    private static function replaceUsernameCallback($matches) {
        $username = $matches[1];
        // Check if username exists in the database
        // Replace this with your actual database query
        $userExists = DB::connection('database_Mirny_Ostrov')->table('users')->where('username', $username)->first();

        // If the user exists, return the link, otherwise return the original username
        if ($userExists) {
            return '<a href="/cencus/'.$userExists->user_id.'/'.$username.'/1">@'.$username.'</a>';
        } else {
            return '@'.$username;
        }
    }

    public static function linkify($text) {
        // Replace hashtags with links
        $text = self::replaceHashtagsWithLinks($text);

        // Replace usernames with links
        $text = self::replaceUsernamesWithLinks($text);

        // Safelinking URLs with multiple main domains and conditions
        $conditions = [
            'http://coderncomputer.my.id', // Main domain 1
            'https://coderncomputer.my.id', // Main domain 1
            '/',                        // Additional condition
        ];
        $text = self::safelink($text, $conditions);

        return $text;
    }

    private static function safelink($text, $conditions) {
        // Use a regular expression to find all links
        return preg_replace_callback(
            '/<a\s+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/i',
            function ($matches) use ($conditions) {
                // Extract the URL
                $url = $matches[1];

                // Check against each condition
                foreach ($conditions as $condition) {
                    if (strpos($url, $condition) === 0 || preg_match('#^' . preg_quote($condition, '#') . '#', $url)) {
                        // Return the original link if it matches any condition
                        return $matches[0];
                    }
                }
                $encodedUrl = base64_encode($url);
                // Replace the link with a safelink format
                return '<a href="/redirect/' . urlencode($encodedUrl) . '/' . $matches[2] . '">' . $matches[2] . '</a>';
            },
            $text
        );
    }
}
