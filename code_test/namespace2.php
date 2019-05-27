<?php
/**
 * User: djunny
 * Date: 2017-08-30
 * Time: 17:45
 * Mail: 199962760@qq.com
 */
namespace App\Admin\Controllers;

class Controller
{

}


class DistributeGoodsController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index() {
        return Admin::content(function (Content $content) {

            $content->header('分销商品');
            $content->description('分销商品列表');

            $content->body($this->grid());
        });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid() {
        return Admin::grid(Goods::class, function (Grid $grid) {

            $grid->model()->addConditions([
                [
                    'orderBy' => [
                        'goods_id',
                        'desc',
                    ],
                ],
                [
                    'where' => [
                        'commission',
                        '>',
                        '0',
                    ],
                ],
            ]);

            $grid->disableCreation();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $goods_id = $actions->row->goods_id;
                $actions->append('<a href="/admin/goods/' . $goods_id . '/edit"><i class="fa fa-edit"></i></a>');
                $goods = Goods::find($goods_id);
                if (count($goods->spec_values) > 0) {
                    $actions->append(' | <a href="/admin/goodsSpecStorage?&goods_id=' . $goods_id . '"><i class="fa fa-copy"></i></a>');
                }
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });

            $grid->goods_id('商品ID')->sortable();
            $grid->goods_sku('商品SKU');

            $grid->gc_id('商品分类')->display(function ($gc_id) {
                return Category::find($gc_id)->gc_name;
            })->label('primary');

            $grid->brand_id('商品品牌')->display(function ($brand_id) {
                return Brand::find($brand_id)->brand_name;
            })->label();

            $grid->goods_name('商品名称');
            $grid->commission('商品佣金')->display(function ($commission) {
                return $commission . ' %';
            });
            $grid->is_hot('是否推荐')->switch();
            $grid->is_new('是否新品')->switch();
            $grid->status('商品状态')->switch();

            $grid->filter(function ($filter) {
                $filter->disableIdFilter();
                $filter->is('gc_id', '商品分类')->select(Category::selectOptions());
                $filter->like('goods_name', '商品名称');
                $filter->like('goods_sku', '商品SKU');
            });

        });
    }
}
