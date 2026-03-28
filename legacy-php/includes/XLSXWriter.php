<?php
class XLSXWriter
{
    protected $rows = [];

    public function __construct()
    {
    }

    public function writeSheet(array $rows, $sheetName = 'Sheet1')
    {
        $this->rows = $rows;
    }

    public function download($filename)
    {
        // Enforce .xls extension for XML format
        if (substr($filename, -5) === '.xlsx') {
            $filename = substr($filename, 0, -1); // Change .xlsx to .xls
        } elseif (substr($filename, -4) !== '.xls') {
            $filename .= '.xls';
        }

        // Clean buffer to prevent corruption
        if (ob_get_level())
            ob_end_clean();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        echo '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="sCenter">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
  </Style>
  <Style ss:ID="sAbsent">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Interior ss:Color="#FFCCCC" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="sLeave">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Interior ss:Color="#FFFFCC" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="sExtra">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Interior ss:Color="#E0E7FF" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Sheet1">
  <Table>';

        foreach ($this->rows as $r => $row) {
            echo '<Row>';
            foreach ($row as $c => $cell) {
                // Determine style
                // Map: 0=Default, 1=sCenter, 2=sAbsent, 3=sLeave
                $val = is_array($cell) ? $cell['value'] : $cell;
                $styleIdx = is_array($cell) && isset($cell['style']) ? $cell['style'] : 0;

                $styleID = "Default";
                if ($styleIdx == 1)
                    $styleID = "sCenter";
                if ($styleIdx == 2)
                    $styleID = "sAbsent";
                if ($styleIdx == 3)
                    $styleID = "sLeave";
                if ($styleIdx == 4)
                    $styleID = "sExtra";

                // Escape value
                $val = htmlspecialchars($val);

                // Determine type
                $type = is_numeric($val) ? 'Number' : 'String';

                echo '<Cell ss:StyleID="' . $styleID . '"><Data ss:Type="' . $type . '">' . $val . '</Data></Cell>';
            }
            echo '</Row>';
        }

        echo '  </Table>
 </Worksheet>
</Workbook>';
        exit;
    }
}
?>