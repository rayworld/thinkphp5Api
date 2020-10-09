<?php

namespace app\api\controller\v1;

class Blog extends common
{
    /**
     * 新增博客
     *
     * @return void [空]
     */
    public function insert()
    {
        $this->validate_request('post');
        $data = $this->params;
        $res = db('article')->insertGetId($data);
        if ($res) {
            $this->return_msg(200, '新建文章成功');
        } else {
            $this->return_msg(400, '新建文章失败');
        }
    }

    /**
     * 更新博客
     *
     * @return void [空]
     */
    public function update()
    {
        $this->validate_request('put');
        $data = $this->params;
        $res = db('article')
            ->where('article_id', $data['article_id'])
            ->update($data);
        if ($res) {
            $this->return_msg(200, '更新文章成功');
        } else {
            $this->return_msg(400, '更新文章失败');
        }
    }

    /**
     * 得到一条博客
     *
     * @return void [空]
     */
    public function get_article_by_id()
    {
        $this->validate_request('get');
        $data = $this->params;
        $where['is_del'] = 0;
        $join = [['api_user u', 'a.author_id = u.user_id']];
        $field = "a.article_id, a.title, a.content, a.comment, u.nick_name";
        $res = db('article')
            ->alias('a')
            ->where($where)
            ->join($join)
            ->field($field)
            ->find();
        if ($res) {
            $this->return_msg(200, '取得文章信息成功', $res);
        } else {
            $this->return_msg(400, '取得文章信息失败', $res);
        }
    }

    /**
     * 删除博客
     *
     * @return void [空]
     */
    public function delete()
    {
        $this->validate_request('delete');
        $data = $this->params;
        //软删除
        $res = db('article')
            ->where('article_id', $data['article_id'])
            ->setField('is_del', 1);
        if ($res) {
            $this->return_msg(200, '删除文章成功');
        } else {
            $this->return_msg(400, '删除文章失败');
        }
        //物理删除

    }

    /**
     * 得到博客列表
     *
     * @return void [空]
     */
    public function get_list()
    {
        $this->validate_request('get');
        $data = $this->params;
        if (!isset($data['page'])) {
            $data['page'] = 10;
        }
        if (!isset($data['num'])) {
            $data['num'] = 1;
        }

        $where['is_del'] = 0;
        $where['author_id'] = $data['user_id'];
        $record_count = db('article')
            ->where($where)
            ->page($data['num'], $data['page'])
            ->count();
        if ($record_count == 0) {
            $this->return_msg(200, '未查询到相关数据');
        }
        $page_count = ceil($record_count / $data['page']);
        $join = [['api_user u', 'a.author_id = u.user_id']];
        $field = "a.article_id, a.title, a.content, a.comment, u.nick_name";
        $res = db('article')
            ->alias('a')
            ->where($where)
            ->join($join)
            ->field($field)
            ->page($data['num'], $data['page'])
            ->select();
        if ($res) {
            $return_data['page_index'] = $data['page'];
            $return_data['page_index'] = $data['num'];
            $return_data['record_count'] = $record_count;
            $return_data['page_count'] = $page_count;
            $return_data['data'] = $res;
            $this->return_msg(200, '取得文章列表成功', $return_data);
        } else {
            $this->return_msg(400, '取得文章列表失败');
        }
    }
}
