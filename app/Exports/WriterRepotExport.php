<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class WriterRepotExport implements FromCollection, WithColumnFormatting, ShouldAutoSize, WithEvents
{
    protected $data;
    const FORMAT_CUSTOM = '0';

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection($this->createData());
    }

    public function createData()
    {
        return $this->data;
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_00, //金额保留两位小数
            'B' => self::FORMAT_CUSTOM, //自定义格式
        ];
    }

    /**
     * 注册事件
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class  => function(AfterSheet $event) {
                //设置作者
//                $event->writer->setCreator('Patrick');
                //设置列宽
//                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(50);
                //设置行高，$i为数据行数
                $count = count($this->data);
                $event->sheet->getDelegate()->getRowDimension(0)->setRowHeight(50);
                for ($i = 1; $i<=$count; $i++) {
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(30);
                }
                //设置区域单元格字体、颜色、背景等，其他设置请查看 applyFromArray 方法，提供了注释
                $event->sheet->getDelegate()->getStyle('A1:E1')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'italic' => false,
                        'strikethrough' => false,
                        'color' => [
                            'rgb' => '000000'
                        ]
                    ],
//                    'fill' => [
//                        'fillType' => Fill::FILL_GRADIENT_LINEAR, //线性填充，类似渐变
//                        'rotation' => 45, //渐变角度
//                        'startColor' => [
//                            'rgb' => '54AE54' //初始颜色
//                        ],
//                        //结束颜色，如果需要单一背景色，请和初始颜色保持一致
//                        'endColor' => [
//                            'argb' => '54AE54'
//                        ]
//                    ]
                ]);

                //合并单元格
//                $event->sheet->getDelegate()->mergeCells('A1:I1');
                //设置区域单元格垂直居中
//                $event->sheet->getDelegate()->getStyle('A1:I1')->getAlignment()->setVertical('center');
//                $event->sheet->getDelegate()->getStyle('A1:I1')->getAlignment()->setHorizontal('center');
            }
        ];
    }
}
