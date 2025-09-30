<?php

namespace App\Services\CreateSnapAds;

class SnapTargetingBuilder
{
    protected array $options;
    protected array $targeting = [
        "regulated_content" => false,
        "demographics" => [],
        "geos" => [],
        "devices" => [],
        "interests" => [],
    ];

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function build(): array
    {
        $this->setAges();        // gender
        $this->setCountry();     // from options
        $this->setDevices();     // from options
        $this->setInterests();   // from DB column

        return $this->targeting;
    }

    protected function setAges(): void
    {
        if (!empty($this->options["age_min"]) && !empty($this->options["age_max"])) {
            $this->targeting["demographics"][] = [
                "min_age"   => (int) $this->options["age_min"],
                "max_age"   => (int) $this->options["age_max"],
                "operation" => "INCLUDE",
            ];
        }
    }



    protected function setCountry(): void
    {
        if (!empty($this->options["country"])) {
            $this->targeting["geos"][] = [
                "country_code" => strtolower($this->options["country"]),
                "operation"    => "INCLUDE",
            ];
        }
    }



    protected function setDevices(): void
    {
        $osMap = [
            "1" => "iOS",
            "2" => "ANDROID",
            "3" => "WEB",
        ];

        if (!empty($this->options["os_type"])) {
            foreach ($this->options["os_type"] as $os) {
                if (isset($osMap[$os])) {
                    $this->targeting["devices"][] = [
                        "os_type"   => $osMap[$os],
                        "operation" => "INCLUDE",
                    ];
                }
            }
        }
    }

    protected function setInterests(): void
    {
        if (!empty($this->options["interests"])) {
            $interests = is_string($this->options["interests"])
                ? json_decode($this->options["interests"], true)
                : $this->options["interests"];

            if (!empty($interests)) {
                foreach ($interests as $interest) {
                    $this->targeting["interests"][] = [
                        "category_id" => [$interest],
                        "operation"   => "INCLUDE",
                    ];
                }
            }
        }
    }
}
