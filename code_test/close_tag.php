<?php

/**
 * Created by PhpStorm.
 * User: @van
 * Date: 2015/4/23
 * Time: 15:05
 */
class FollowRecord
{
    const TYPE_RECORD  = 1;//跟进纪录
    const TYPE_PLAN    = 2;//跟进计划
    const TYPE_SERVICE = 3;//跟进服务
    const TYPE_REBACK  = 4;//回访

    public function searchNotice() {
        $search            = new CSearch();
        $search->condition = 'uid = ? and pre_time > 0 and pre_time < ? and pre_ok = 0';
        $search->join      = 'left join t_client  as client on client.id = t.cli_id';
        $search->params    = array(User::getCuruid(), time());
        $search->select    = 't.*,client.name cli_name';
        return $this->queryAllSearch($search);
    }

    public function searchReback($s_params) {
        $search    = new CSearch();
        $condition = 'cli_id = ? and is_reback = 1';
        $params    = array($s_params['cli_id']);
        if ($s_params['start_time']) {
            $condition .= ' and t.time >= ? ';
            $params[] = strtotime($s_params['start_time']);
        }
        if ($s_params['end_time']) {
            $condition .= ' and t.time <= ? ';
            $params[] = strtotime($s_params['end_time']) + 86400;
        }
        if ($s_params['reback_type_id']) {
            $condition .= ' and t.fid = ? ';
            $params[] = $s_params['reback_type_id'];
        }
        if ($s_params['uid']) {
            $condition .= ' and t.uid = ? ';
            $params[] = $s_params['uid'];
        }
        $s_sort            = Common::getSortStr($params, array(
            'rebacktime' => 't.reback_time'
        ));
        $search->order     = $s_sort;
        $search->condition = $condition;
        $search->params    = $params;
        return $this->queryAllSearch($search);
    }

    public static function hasNotice() {
        return self::model()->count('uid = ? and pre_time > 0 and pre_time < ? and pre_ok = 0', array(User::getCuruid(), time()));
    }

    public function getOneById($id) {
        return $this->query('id=?', $id);
    }

    public function add($param) {
        $param['com_id'] = User::getCurcomid();
        $param['c_time'] = time();
        $param['e_time'] = time();
        return $this->insert($param);
    }

    public function edit($param, $id) {
        $param['com_id'] = User::getCurcomid();
        $param['e_time'] = time();
        return $this->update($param, 'id = ?', $id);
    }

    /**
     * 通过ID查询记录
     *
     * @param int $com_id 公司ID
     * @param int $id     主键ID
     *
     * @return array
     */
    public static function findById($com_id, $id) {
        $res = null;
        if ($id > 0) {
            $res = self::model()->query('com_id = ? and id = ?', array($com_id, $id));
        }
        return $res;
    }

    public function getTodyFollow() {
        return $this->query('com_id = ?  and c_time >' . mktime(0, 0, 0, date('m'), date('d'), date('Y')) . ' and c_time <' . (mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1), array(User::getCurcomid()), 'count(*) as number');

    }


}

?>