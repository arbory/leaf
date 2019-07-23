<?php


namespace Arbory\Base;

use Illuminate\Support\Collection;

class RedirectHealthChecker
{

    /**
     * @var Collection
     */
    private $redirectsCollection;

    /**
     * @var array
     */
    private $invalid = [];

    /**
     * @var array
     */
    private $valid = [];

    /**
     * @var array
     */
    private $errors = [];

    /**
     * RedirectHealthChecker constructor.
     * @param Collection $redirectsCollection
     */
    public function __construct(Collection $redirectsCollection)
    {
        $this->redirectsCollection = $redirectsCollection;
    }

    /**
     * Check redirect urls and fill out info values
     *
     * return void
     */
    public function check() {

        foreach ($this->redirectsCollection as $redirect) {
            $url = url($redirect->to_url);

            if($this->isPageAlive($url)) {
                $this->valid[$url] = $redirect->id;
            } else {
                $this->invalid[$url] = $redirect->id;
            }
        }
    }

    /**
     * Make curl request to url and check it is alive
     *
     * @param $url
     * @return bool
     */
    public function isPageAlive($url) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 1000
        ]);

        if (!curl_exec($curl)) {
            $this->errors[$url] = 'Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
            return false;
        }

        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return $responseCode === 200;
    }

    /**
     * @return int
     */
    public function getValidCount() {
        return count($this->valid);
    }

    /**
     * @return int
     */
    public function getInvalidCount() {
        return count($this->invalid);
    }

    /**
     * @return array
     */
    public function getValidIds() {
        return array_values($this->valid);
    }

    /**
     * @return array
     */
    public function getInvalidIds() {
        return array_values($this->invalid);
    }

    /**
     * @return array
     */
    public function getInvalidUrlList() {
        return array_keys($this->invalid);
    }

    /**
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
}