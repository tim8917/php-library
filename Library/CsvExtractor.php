<?php

namespace TAminev\PhpLibrary;

class CsvExtractor
{
    const EXTRACT_TO_MEMORY = 0;

    const EXTRACT_TO_FILE = 1;

    private $input;

    private $skipLines;

    private $debug = true;

    public function __construct($input, $skipLines = 0){
        $this->input = $input;
        $this->skipLines = $skipLines;
    }

    /**
     * Extracts columns from $this->input to an array of rows.
     * @param array $columns array(0,3,5);
     * @return array
     */
    public function extract($columns = array())
    {
        $result = array();

        $handle = fopen($this->input, 'r');

        for ($i=0; $i<$this->skipLines; $i++){
            fgetcsv($handle);
        }

        while (($data = fgetcsv($handle)) != false){
            $row = array();
            if ($columns){
                foreach ($columns as $column){
                    $row[] = $data[$column];
                }
            } else {
                $row = $data;
            }
            $count = count($row);
            for($i=0; $i<$count; $i++){
                $row[$i] = trim($row[$i], " \t\n\r\0\x0B\"'");
            }
            $result[] = $row;
        }
        fclose($handle);
        return $result;
    }

    /**
     * Writes an array of rows to the $output file in csv format.
     *
     * @param $array
     * @param $output
     *
     */
    public static function write($array, $output)
    {
        $o = fopen($output ,'w');
        foreach ($array as $row)
        {
            fputcsv($o, $row);
        }
        fclose($o);
    }

    /**
     * Iterates over configs and extracts columns from the $this->input to file $configs[i]['output'].
     *
     * @param $configs array(
     *                  array('columns' => array(2, 17), 'output' => '...'),
     *                  array('columns' => array(4, 5), 'output' => '...' ),
     *                  ...
     *              )
     */
    public function extractMultiple($configs)
    {
        $handle = fopen($this->input, 'r');
        foreach ($configs as $key=>$config){
            $arr = $this->extract($config['columns']);
            ArrayHelper::removeRowsByKeyWithEmptyValue($arr, 0);
            $output = fopen($config['output'] ,'w');
            foreach ($arr as $row)
            {
                fputcsv($output, $row);
            }
            fclose($output);
            if ($this->debug){
                var_dump(array("Extracted " . count($arr) . " rows. \n", $config['output']));
                echo "\n";
            }
        }
        fclose($handle);
    }

}