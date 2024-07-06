<?php
// user_limits.php
require_once '../configurazione/conn.php';

class UserLimits {
    private $isPremium;
    private $maxBlogsNonPremium = 1;
    private $maxPostsPerBlogNonPremium = 1;
    private $maxDailyCommentsNonPremium = 20;

    public function __construct($isPremium) {
        $this->isPremium = $isPremium;
    }

    public function canCreateBlog($userId) {
        if ($this->isPremium) {
            return true; // Gli utenti premium possono creare blog illimitati
        }

        // Verifica se l'utente ha già un blog
        $numBlogs = $this->countUserBlogs($userId);
        return $numBlogs < $this->maxBlogsNonPremium;
    }

    public function canAddPost($blogId) {
        if ($this->isPremium) {
            return true; // Gli utenti premium possono aggiungere post illimitati
        }

        // Verifica il numero di post per il blog dell'utente non premium
        $numPosts = $this->countBlogPosts($blogId);
        return $numPosts < $this->maxPostsPerBlogNonPremium;
    }

    public function canAddComment($userId) {
        if ($this->isPremium) {
            return true; // Gli utenti premium possono commentare illimitatamente
        }

        // Verifica il numero di commenti fatti oggi dall'utente non premium
        $numCommentsToday = $this->countDailyComments($userId);
        return $numCommentsToday < $this->maxDailyCommentsNonPremium;
    }

    private function countUserBlogs($userId) {
        // Implementa la logica per contare i blog dell'utente nel database
        // Esempio di implementazione: conta i blog dell'utente dall'appropriato database
         // File con le informazioni di connessione al database

       

        $query = "SELECT COUNT(*) AS num_blogs FROM blog WHERE id_utente = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $numBlogs = $row['num_blogs'];
        $stmt->close();
        $conn->close();

        return $numBlogs;
    }

    private function countBlogPosts($blogId) {
        // Implementa la logica per contare i post del blog nel database
        // Esempio di implementazione: conta i post del blog dall'appropriato database
        require_once '../configurazione/conn.php'; // File con le informazioni di connessione al database

       

        $query = "SELECT COUNT(*) AS num_posts FROM post WHERE id_blog = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $blogId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $numPosts = $row['num_posts'];
        $stmt->close();
        $conn->close();

        return $numPosts;
    }

    private function countDailyComments($userId) {
        // Implementa la logica per contare i commenti giornalieri dell'utente nel database
        // Esempio di implementazione: conta i commenti dell'utente oggi dall'appropriato database
        require_once '../configurazione/conn.php'; // File con le informazioni di connessione al database

       

        $today = date('Y-m-d');
        $query = "SELECT COUNT(*) AS num_comments FROM commento WHERE id_utente = ? AND DATE(data_comm) = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $numCommentsToday = $row['num_comments'];
        $stmt->close();
        $conn->close();

        return $numCommentsToday;
    }
}
?>