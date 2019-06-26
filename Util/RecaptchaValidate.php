<?php


class RecaptchaValidate
{
    const URL_VERIFY = "https://www.google.com/recaptcha/api/siteverify";

    /**
     * @var string
     */
    private $token;

    private $secretKey;

    public function __construct(string $secretKey,string $token)
    {
        $this->token = $token;
        $this->secretKey = $secretKey;
    }

    public function validateCaptcha()
    {
        $data = array('secret' => $this->secretKey, 'response' => $this->token);

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $response = file_get_contents(self::URL_VERIFY, false, $context);
        $responseKeys = json_decode($response,true);

        return $responseKeys['success'];

    }

}