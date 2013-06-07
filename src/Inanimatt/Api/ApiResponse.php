<?php
namespace Inanimatt\Api;

use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

class ApiResponse extends BaseJsonResponse
{
    public function __construct($data = null, $status = 200, $headers = array(), $version = null)
    {
        parent::__construct($data, $status, $headers);
        $this->setVersion($version);
    }

    public function setVersion($version)
    {
        $this->headers->set('X-API-Version', $version);
    }

    public function setDocumentation($url)
    {
        $this->headers->set('Link', sprintf('"%s"; rel="help"', $url), false);
    }

    public function setDeprecated($isDeprecated)
    {
        $warning = 'This method is deprecated.';

        if ($isDeprecated) {
            $this->headers->set('X-API-Message', $warning, false);
        } else {
            $messages = $this->headers->get('X-API-Message', array(), false);
            if (($key = array_search($warning, $messages)) !== false) {
                unset($messages[$key]);
                $this->headers->set('X-API-Message', $messages);
            }
        }
    }

    /**
     * Like the parent class, this sets the JSON response. However it pretty-prints it instead.
     * Minified isn't worth the bandwidth saving, and compression can recover most of it anyway.
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    public function setData($data = array())
    {
        // Encode <, >, ', &, and " for RFC4627-compliant JSON, which may also be embedded into HTML.
        $this->data = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PRETTY_PRINT);

        return $this->update();
    }
}