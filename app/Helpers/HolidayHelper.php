use Yasumi\Yasumi;

class HolidayHelper
{
    public static function isHoliday($date)
    {
        $holidays = Yasumi::create('Japan', $date->year);
        return $holidays->isHoliday($date);
    }
}