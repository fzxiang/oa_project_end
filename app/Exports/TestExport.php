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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TestExport implements FromCollection, WithColumnFormatting, ShouldAutoSize, WithEvents
{
    protected $data;

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
        return [
            ['编辑', '姓名', '年龄'],
            [1, '小米', '34'],
            [2, '小明', '44'],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY, //日期
            'C' => NumberFormat::FORMAT_NUMBER_00, //金额保留两位小数
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
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(50);
                //设置行高，$i为数据行数
                for ($i = 0; $i<=1265; $i++) {
                    $event->sheet->getDelegate()->getRowDimension($i)->setRowHeight(50);
                }
                //设置区域单元格垂直居中
                $event->sheet->getDelegate()->getStyle('A1:K1265')->getAlignment()->setVertical('center');
                //设置区域单元格字体、颜色、背景等，其他设置请查看 applyFromArray 方法，提供了注释
                $event->sheet->getDelegate()->getStyle('A1:K6')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'italic' => false,
                        'strikethrough' => false,
                        'color' => [
                            'rgb' => '808080'
                        ]
                    ],
                    'fill' => [
                        'fillType' => 'linear', //线性填充，类似渐变
                        'rotation' => 45, //渐变角度
                        'startColor' => [
                            'rgb' => '000000' //初始颜色
                        ],
                        //结束颜色，如果需要单一背景色，请和初始颜色保持一致
                        'endColor' => [
                            'argb' => 'FFFFFF'
                        ]
                    ]
                ]);
                //合并单元格
                $event->sheet->getDelegate()->mergeCells('A1:B1');
            }
        ];
    }
}
