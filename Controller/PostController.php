<?php

namespace BlogEcrivain\Controller;

// On indique les espace de nom des classes utilisées.

use BlogEcrivain\Model\Entity\Post;
use BlogEcrivain\Model\Manager\CommentManager;
use BlogEcrivain\Model\Manager\PostManager;
use BlogEcrivain\Model\Manager\UserManager;

use BlogEcrivain\View\View;

class PostController
{
    public $post;

    public $comment;

    public $user;

    private $last_page;

    private $num_page;

    private $total_posts;

    private $num_max_before_after = 2;

    private $posts_by_page = 4;

    /**
     *   NAVIGATION ENTRE LES PAGES
     *
     **/


    public function showbiblio()
    {
        include(__DIR__ . "/../View/Frontend/biblio.php");
    }

    public function formInscription()
    {
        include(__DIR__ . "/../View/Frontend/form_inscription.php");
    }

    public function formConnexion()
    {
       include(__DIR__ . "/../View/Frontend/form_connexion.php");
    }

                    /**
                     *
                     **  GESTION DES POSTS
                     *
                     **/

    public function formCreatePost()
    {
        //appelle la page d'accueil
        include(__DIR__ . "/../View/Backend/form_CreatePost.php");
    }


    public function createPost($contenu)
    { // 1 - GESTION ET ENVOI DES INFOS DANS LA BDD;
        // Donc instanciation de la class
        // Envoi des informations récupérer dans les POST par la méthode SET***

        $post = new Post();

        $post->setTitle($contenu['title']);
        $post->setImg($_FILES['img']['name']);
        $post->setContent($contenu['content']);

        //envoi des informations à la db via la fonction save du manager
        // donc j'instancie la classe ContactManager

        $postManager = new PostManager;
        $saveIsOk = $postManager->save($post);

        if($saveIsOk){
            $message = 'Votre article a été bien ajouté à la base de données';

        } else{
            $message = 'Votre article n\'a pas pu être ajouté à la base de données';
        }
        // NB: Il faut que je retourne le résultat en HTLM. Je pense que ça doit etre au niveau de la vue.

        // 2 - TRAITEMENT DE L'IMAGE ( Envoi de l'image dans mon dossier imgUpload)
        $this->saveImg();

        include(__DIR__ . "/../View/Backend/messageAdmin.php");
    }

    public function readLastPost()
    {
        // On instancie la classe ContactManager et on appelle la méthode readAll
        $postManager = new PostManager();

        $posts = $postManager->readLastAll();

        // Je pouvais faite des traitements conditionnels ici mais je l'ai fait dans la vue

        $view= new View();
        $view->showFrontPage("readLastPosts", $posts);


        // On affiche ensuite le résultat en HTML en appellant ma vue depuis mon controlleur-ci.
        //include(__DIR__ . "/../View/Frontend/readLastPosts.php");
    }

    public function readAllPosts()
    {
        // On instancie la classe ContactManager et on appelle la méthode readAll
        $postManager = new PostManager();

        $posts = $postManager->readAll();

        // Je pouvais faite des traitements conditionnels ici mais je l'ai fait dans la vue

        $view= new View();
        $view->ShowFrontPage("ReadAllPosts", $posts);


        // On affiche ensuite le résultat en HTML en appellant ma vue depuis mon controlleur-ci.
       // include(__DIR__ . "/../View/Frontend/readAllPosts.php");
    }

    public function readPost($reception)
    {
        // 1- LES POST
        $postManager = new PostManager();
        $post = $postManager->read($reception);

        // Je pouvais faite des traitements conditionnels ici mais je l'ai fait dans la vue

        //  2- LES COMMENTAIRES
        $commentManager = new CommentManager();
        $comments = $commentManager->readAllByID($reception);

        $view= new View();
        $view->ShowFrontPage("readPost", [$post, $comments]);

        //include(__DIR__ . "/../View/Frontend/readPost.php");
    }

    /**
     * recoit les informations
     * il lit les informations
     * il demande à la vue d'afficher le formulaire avec les infos à l'intérieur
     **/

    public function formUpdatePost($postModif)
    {
        // intentiation de ContactManager pour pouvoir read les infos

        $postManager = new PostManager();
        $post = $postManager->read($_GET['id']);

        // IL doit aussi demandé le nouveau formulaire à la vue selon l'id
        include(__DIR__ . "/../View/Backend/form_UpdatePost.php");
    }

    public function updatePost()
    {
        // Je cré un nouvel object avec la classe ContactManager;
        // j'instancie ensuite la fonction read de l'object contactManager;
        $postManager = new PostManager();
        $post= $postManager->read($_POST['id']);


        // J'envoi en les infos aux differents élements de la classe contact

        $post->setTitle($_POST['title']);
        $post->setImg($_FILES['img']['name']);
        $post->setContent($_POST['content']);

        // Je sauvegarde mes informations dans la base de données

        $saveIsOk = $postManager->save($post);

        if($saveIsOk){
            $message = 'Félicitation, votre artcile a été bien modifiée';
        }else{
            $message = 'Alerte, une erreur est survenue au niveau de updatePost';
        }

        //NB: il faut que je retroune le réslutat en HTML
        include(__DIR__ . "/../View/Backend/messageAdmin.php");

        // 2 - TRAITEMENT DE L'IMAGE ( Envoi de l'image dans mon dossier imgUpload)
        $this->saveImg();
    }

    public function saveImg()
    {
        // Testons si le fichier a bien été envoyé et s'il n'y a pas d'erreur
        if (isset($_FILES['img']) AND $_FILES['img']['error'] == 0)
        {
            // Testons si le fichier n'est pas trop gros
            if ($_FILES['img']['size'] <= 1000000)
            {
                // Testons si l'extension est autorisée
                $infosfichier = pathinfo($_FILES['img']['name']);
                $extension_upload = $infosfichier['extension'];
                $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png');
                if (in_array($extension_upload, $extensions_autorisees))
                {
                    // On peut valider le fichier et le stocker définitivement
                    $executeIsOk= move_uploaded_file($_FILES['img']['tmp_name'], __DIR__ .'/../Public/imgUpload/'.basename($_FILES['img']['name']));

                    if($executeIsOk)
                    {
                        echo "L'envoi de l'image a bien été effectué !";
                    }
                    else
                    {
                        echo "Il y a un probleme au niveau de l'envoi du fichier image dans la BDD";
                    }
                }
                else
                {
                    echo "l'extention de votre image n'est pas pris en charge";
                }
            }
            else
            {
                echo ' La taille du fichier est trop grand';
            }
        }
        else
        {
            echo 'le fichier image nexiste pas ou il y a une erreur';
        }
    }

    public function deletePost($recupPost)
    {
        $postManager = new PostManager();

        $deleteIsOk = $postManager->delete($recupPost);

        if($deleteIsOk){
            $message = 'L\'article été bien supprimé';
        }else
        {
            $message = 'Une erreur est arrivée. Impossible de supprimer cet article';
        }
        //NB: il faut que je retroune le réslutat en HTML
        include(__DIR__ . "/../View/Backend/messageAdmin.php");
    }



    public function readAllPostsByPage()
    {
        // Je récupère le nombre total de Post ($total_Post)
        $postManager= new PostManager();
        $this->total_posts = $postManager->totalPosts();

        // Je récupère le nombre total de page
        $this->last_page = ceil($this->total_posts/$this->posts_by_page);

        // condition Pour lire la pagination

        if(isset($_GET['p']) && is_numeric($_GET['p']))
        {
            $this->num_page = $_GET['p'];
        }
        else
        {
            $this->num_page = 1;
        }

        if($this->num_page <= 1)
        {
            $this->num_page = 1;
        }
        elseif($this->num_page > $this->last_page)
        {
            $this->num_page= $this->last_page;
        }

        // LECTURE DES ARTICLES 8x8
        $postManager = new PostManager();
        $posts = $postManager->readAllByPage();


        include(__DIR__ . "/../View/Frontend/readAllPosts.php");
    }


    public function readAllAdmin()
    {
        // 1- les posts
        $postManager= new PostManager();
        $posts= $postManager->readAll();

        // 2- Les commentaires
        $commentManager = new CommentManager();
        $comments= $commentManager->readAllSignale();

        // 3- Les utilisateurs
        $userManager = new UserManager();
        $users= $userManager->readAll();

        $view= new View();
        $view->ShowBackPage("admin", [$posts, $comments, $users]);

        //include(__DIR__ . "/../View/Backend/admin.php");
    }


    public function BackReadCat($the_choix)
    {
        // 1- les posts
        $postManager= new PostManager();
        $posts= $postManager->readAll();

        // 2- Les commentaires
        $commentManager = new CommentManager();
        $comments= $commentManager->readAllByStat($the_choix);

        // 3- Les utilisateurs
        $userManager = new UserManager();
        $users= $userManager->readAll();

        $view= new View();
        $view->ShowBackPage("admin", [$posts, $comments, $users]);
    }

}