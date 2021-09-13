<?php

namespace Osama\DB;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeedGenerator
{
    private $fields = [];
    private $totalRows = 10;
    private $perTrans = 10;
    private $tableName = '';
    public $arabicLocale = false;
    private $faker;
    private $imageList = [];
    private $showImageMessage = false;

    /**
     * Undocumented function
     *
     * @param string  $tableName
     * @param integer $totalRows
     * @param array   $fields   should be ['fieldName' => ['username', length], ['varchar', length], ['pass'], ['number', min, max], ['phone'], ['id', array]] supported is number, varchar
     * @param boolean $arabicLocale
     * @param integer $perTrans
     */
    public function __construct($arabicLocale = false, $perTrans = 10)
    {
        $this->perTrans = $perTrans;
        $this->faker = \Faker\Factory::create($arabicLocale ? 'ar_JO' : \Faker\Factory::DEFAULT_LOCALE);
        $this->faker->addProvider(new \Faker\Provider\Internet($this->faker));
        $tmp = glob("./public/files/*.jpg");
        foreach ($tmp as $val) {
            $this->imageList[] = str_replace('./public/files/', "", $val);
        }

    }

    public function run($tableName, $totalRows, $fields)
    {

        for ($i = 0; $i < $totalRows; $i += $this->perTrans) {
            $array = [];
            for ($k = 0; $k < $this->perTrans; $k++) {
                // Prepare the array
                // fill feilds
                $time = Carbon::now()->toDateTimeString();
                $tmp = [
                    'created_at' => $time,
                    'updated_at' => $time,
                ];
                foreach ($fields as $field => $crateria) {
                    switch ($crateria[0]) {
                        case 'username':
                            $tmp[$field] = substr($this->faker->userName, 0, $crateria[1] ?? 45);
                            break;
                        case 'varchar':
                            $tmp[$field] = $this->faker->text($crateria[1] ?? 45);
                            break;
                        case 'pass':
                            $tmp[$field] = Hash::make("123");
                            break;
                        case 'number':
                            $tmp[$field] = rand($crateria[1] ?? 0, $crateria[2] ?? 999);
                            break;
                        case 'phone':
                            $tmp[$field] = "077" . rand(10000000, 19999999);
                            break;
                        case 'id':
                            $tmp[$field] = $crateria[1][rand(0, count($crateria[1]) - 1)];
                            break;
                        case 'image':
                            $tmp[$field] = $this->imageList[rand(0, count($this->imageList) - 1)];
                            $this->showImageMessage = true;
                            break;
                    }
                }
                $array[] = $tmp;
            }
            // Execute the array insert
            DB::table($tableName)->insert($array);
        }
        echo "[+] Inserted $totalRows rows to $tableName\r\n";
        if ($this->showImageMessage) {
            echo "[+][+] Images are detected, move the images to public/files\r\n";
        }
        $this->showImageMessage = false;
        return DB::table($tableName)->pluck('id')->toArray();
    }

}
