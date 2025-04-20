<?php
namespace Contingent;

class Captha
{
    private $width;
    private $height;
    private $font_size;
    private $length;
    private $characters;

    public function __construct($width = 250, $height = 100, $font_size = 26, $length = 6)
    {
        $this->width = $width;
        $this->height = $height;
        $this->font_size = $font_size;
        $this->length = $length;
        $this->characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Avoid similar characters like O, 0, I, 1
    }

    public function generateCaptchaString()
    {
        $captcha_string = '';
        for ($i = 0; $i < $this->length; $i++) {
            $captcha_string .= $this->characters[mt_rand(0, strlen($this->characters) - 1)];
        }
        $_SESSION['captcha'] = $captcha_string;
        return $captcha_string;
    }

    public function createImage($font_path = __DIR__ . '/fonts/arial.ttf')
    {
        $captcha_string = $this->generateCaptchaString();

        // Create image
        $image = imagecreatetruecolor($this->width, $this->height);

        // Colors
        $background_color = imagecolorallocate($image, 255, 255, 255); // White background
        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $background_color);

        // Add random lines for noise
        for ($i = 0; $i < 20; $i++) {
            $line_color = imagecolorallocate($image, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
            imageline($image, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $line_color);
        }

        // Add text with random distortion
        for ($i = 0; $i < $this->length; $i++) {
            $text_color = imagecolorallocate($image, mt_rand(0, 150), mt_rand(0, 150), mt_rand(0, 150));
            $angle = mt_rand(-40, 40);
            $x = 20 + $i * 35;
            $y = mt_rand(40, 90);
            imagettftext($image, $this->font_size + mt_rand(-4, 4), $angle, $x, $y, $text_color, $font_path, $captcha_string[$i]);
        }

        // Add noise (dots)
        for ($i = 0; $i < 3000; $i++) {
            $noise_color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($image, mt_rand(0, $this->width), mt_rand(0, $this->height), $noise_color);
        }

        // Output the CAPTCHA image as base64
        ob_start();
        imagepng($image);
        $image_data = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($image_data);
    }

    public function validateCaptcha($input)
    {
        return isset($_SESSION['captcha']) && $input === $_SESSION['captcha'];
    }

    // Function to output the HTML for CAPTCHA
    public static function displayCaptchaForm()
    {
        $captcha = new self();
        $captcha_img = $captcha->createImage();

        return "
            <center><img src='{$captcha_img}' alt='CAPTCHA Image'></center><br>
            <input autocomplete='off' name='captha' type='text' class='form-control' id='captha' placeholder='Captcha' required>
        ";
    }
}