<?php
/**
 * Created by PhpStorm.
 * User: BourgineMac
 * Date: 04/03/2018
 * Time: 23:11
 */

namespace BlogEcrivain\Model\Manager;

// Je definis l'emplacement des classe je vais utiliser

use BlogEcrivain\Model\Entity\Post;
use PDO;

class PostManager extends Connex_Db
{
    private $pdoStatement;

    private $posts_by_page = 4;

    private $num_max_before_after = 2;

    private $total_posts;

    private $last_page;

    private $num_page;


    /**
     *
     **/

    private function create(Post &$post)
    {
        //Préparation de la req
        //je lie pdoStatement à pdo car je fais une req préparée
        $this->pdoStatement=$this->pdo->prepare('INSERT INTO post VALUES(NULL, :title, :img, :content, now())');

        //liaison des paramettres : Liaison des name du formulaire aux champs de la table post
        $this->pdoStatement->bindValue(':title', $post->getTitle(), PDO::PARAM_STR);
        $this->pdoStatement->bindValue(':img', $post->getImg(), PDO::PARAM_STR);
        $this->pdoStatement->bindValue(':content', $post->getContent(), PDO::PARAM_STR);


        //Exécution de la req
        $executeIsOk = $this->pdoStatement->execute();

        //Recupération du résultat
        if(!$executeIsOk){ // si l'éxécution ne s'est pas bien passée

            return false;
        }
        else{

            //Ajoute l'élement en mettant à jour d'id.
            $id = $this->pdo->lastInsertId();
            $post = $this->read($id);
            return true;
        }
    }

    /**
     *
     **/

    public function read($id)
    {
        //préparation de la req
        $this->pdoStatement=$this->pdo->prepare('SELECT * FROM post WHERE id=:id');

        // liaison de la req
        $this->pdoStatement->bindValue(':id', $id, PDO::PARAM_INT);

        //exécution de la req
        $executeIsOk = $this->pdoStatement->execute();
        if($executeIsOk)
        {

            // récupération du réslutat. Ici, j'utiliserai fetchObject car je n'affiche qu'une seul ligne da db

            $post= $this->pdoStatement->fetchObject('BlogEcrivain\Model\Entity\Post');

            if($post === false)
            {
                return null;
            }
            else
            {
                return $post;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Cette fonction lira toutes les articles pour la back office
     **/

    public function readAll()
    {
        $this->pdoStatement = $this->pdo->query('SELECT * FROM post ORDER BY id DESC ');

        // récupération de résultats tableau. Un tableau se récupère en 3 étapes

        //1- initialisation du tableau vide
        $posts=[];

        // 2-On ajoute au table chaque ligne.
        while($post=$this->pdoStatement->fetchObject('BlogEcrivain\Model\Entity\Post'))
        {
            $posts[]=$post;
        }
        //3- On retourne le table finalisé.
        return $posts;
    }


    public function totalPosts()
    {
        // Nombre total de Posts
        $this->pdoStatement = $this->pdo->query('SELECT id FROM post');
        $this->total_posts =  $this->pdoStatement->rowCount();

        $resultat = $this->total_posts;
        return $resultat;
    }

    /**
     * Cette fonction lira toutes les articles par 8 x 8
     * @param num_page
     **/

    public function readAllByPage()
    {

        $this->total_posts =$this->totalPosts();

        // Le nombre total de PAGE
        $this->last_page = ceil($this->total_posts/$this->posts_by_page);

        // GERER LE $THIS->

        if(isset($_GET['p']) && is_numeric($_GET['p']))
        {
            $this->num_page = $_GET['p'];
        }
        else
        {
            $this->num_page = 1;
        }

        if($this->num_page <=1)
        {
            $this->num_page = 1;
        }

        elseif($this->num_page > $this->last_page)
        {
            $this->num_page = $this->last_page;
        }

        //pagination ici, dans l'index? ?
        //var_dump($num_page);
        // LA REQUETTE

        $depart = ($this->num_page - 1)*$this->posts_by_page;

        //$limit= 'LIMIT '.($this->page_num - 1)* $this->posts_by_page.','.$this->posts_by_page;

        //var_dump($depart);

        $this->pdoStatement = $this->pdo->query("SELECT * FROM post ORDER BY id DESC LIMIT $depart,$this->posts_by_page");

        //1- initialisation du tableau vide
        $posts=[];

        // 2-On ajoute au table chaque ligne.
        while($post=$this->pdoStatement->fetchObject('BlogEcrivain\Model\Entity\Post'))
        {
            $posts[]=$post;
        }

        //3- On retourne le table finalisé.
        return $posts;
    }

    /**
     *
     *  Cette fonction lira les 6 derniers articles à afficher en page d'accueil.
     *
     **/


    public function readLastAll()
    {
        // Connexion à la table pour récuperer la liste des contacts sous forme de tableau

        $this->pdoStatement = $this->pdo->query('SELECT * FROM post ORDER BY id DESC LIMIT 0,6');

        // récupération de résultats tableau. Un tableau se récupère en 3 étapes

        //1- initialisation du tableau vide
        $posts=[];

        // 2-On ajoute au table chaque ligne.
        while($post=$this->pdoStatement->fetchObject('BlogEcrivain\Model\Entity\Post'))
        {
            $posts[]=$post;
        }

        //3- On retourne le table finalisé.
        return $posts;
    }


    /**
     * NB: verifier la liaison des parammettre fait-il mettre l'id?
     **/

    private function update(Post $post)
    {
        //preparation de la req
        $this->pdoStatement = $this->pdo->prepare('UPDATE post set title=:title, img=:img, content=:content WHERE id=:id');
        //Liaison des paramètres des elements de formulaire a ceux des champs de la bdd

        $this->pdoStatement->bindValue(':id', $post->getId(), PDO::PARAM_STR);
        $this->pdoStatement->bindValue(':title', $post->getTitle(), PDO::PARAM_STR);
        $this->pdoStatement->bindValue(':img', $post->getImg(), PDO::PARAM_STR);
        $this->pdoStatement->bindValue(':content', $post->getContent(), PDO::PARAM_STR);

        //execution de la requette
        $executeIsOk= $this->pdoStatement->execute();

        //recuperation du résultat
        return $executeIsOk;
    }

    /**
     *
     **/

    public function delete($id)
    {
        //preparation de la req
        $this->pdoStatement =$this->pdo->prepare('DELETE FROM post WHERE id=:id');

        //liaison des paramettres
        $this->pdoStatement->bindValue(':id', $id, PDO::PARAM_INT);

        // execution de la req
        $executionIsOk = $this->pdoStatement->execute();

        //recupération du résultat.
        return $executionIsOk;
    }


    /**
    La fonction public save est un rAssemblement des fonctions create et de la fonction update.
     * Elle crée un objet contact lorsque qu'il n'y a pas d'id.
     * sinon, elle fait appel à la fonction UPDATE
     */

    public function save(Post &$post)
    {
        if (is_null($post->getId())){
            return $this->create($post);
        }
        else{
            return $this->update($post);
        }
    }






}