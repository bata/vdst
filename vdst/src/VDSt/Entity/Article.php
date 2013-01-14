<?php

namespace VDSt\Entity;

use VDSt\Helper\DateTimeGerman;

class Article
{
    public $id;
    
    public $title;
    
    public $content;
    
    public $lastUpdate;
    
    public static function fetchById(\Doctrine\DBAL\Connection $con, $id)
    {
        $sql = 'SELECT * FROM article WHERE id = ?';
        $mapping = self::map($con->fetchAll($sql, array($id)));
    
        return array_pop($mapping);
    }
    
    public static function fetchByTitle(\Doctrine\DBAL\Connection $con, $title)
    {        
        $sql = 'SELECT * FROM article WHERE title = ?';
        $mapping = self::map($con->fetchAll($sql, array($title)));
        
        return array_pop($mapping);
    }
    
    public static function fetchAll(\Doctrine\DBAL\Connection $con)
    {
        $sql = 'SELECT * FROM article';
        
        return self::map($con->fetchAll($sql));
    }
    
    public static function save(\Doctrine\DBAL\Connection $con, Article $entry)
    {
        $data = array(
            'title' => $entry->title,
            'content' => $entry->content,
            'last_update' => $entry->lastUpdate->format('Y-m-d H:i:s'),
        );
    
        if ($entry->id) {
            $con->update('article', $data, array( 'id' => $entry->id ));
        } else {
            $con->insert('article', $data);
        }
    }
    
    public static function delete(\Doctrine\DBAL\Connection $con, $id)
    {
        $con->delete('article', array('id' => $id));
    }
    
    private static function map($assocData)
    {        
        $resultSet = array();
    
        foreach ($assocData as $data) {
    
            $article = new Article();
            $article->id = $data['id'];
            $article->title = $data['title'];
            $article->content = $data['content'];
            $article->lastUpdate = new DateTimeGerman($data['last_update']);
            	
            array_push($resultSet, $article);
    
        }
    
        return $resultSet;
    }
}