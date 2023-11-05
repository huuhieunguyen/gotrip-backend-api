<?php

namespace App\Utils; // Update the namespace according to your project's structure

use DateTime;
use DateTimeZone;

class TimezoneConverter
{
    // public static function convertToTimezone($utcTime, $timezone)
    // {
    //     $date = new DateTime($utcTime);
    //     $date->setTimezone(new DateTimeZone($timezone));
    //     return $date->format('Y-m-d H:i:s');
    // }

    public static function convertToTimezone($utcTime)
    {
      $timezone = 'Asia/Ho_Chi_Minh'; // Timezone for Vietnam

      try {
        $date = new DateTime($utcTime);
        $date->setTimezone(new DateTimeZone($timezone));
        return $date->format('Y-m-d H:i:s');
      } catch (\Exception $e) {
          // Handle the exception
          // You can log the error, throw a custom exception, or return a default value
          // For example, you can throw a custom exception:
          throw new \Exception('Error converting time zone: ' . $e->getMessage());
      }
    }
}