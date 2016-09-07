<?php
//header('Content-Type: text/plain');
include_once './class/Cookies.php';
include_once './class/Crossword.php';
$app = new Crossword();
?>


<html>
    <head>
        <title>Crossword</title>
        <link rel="stylesheet" href="css/main.css"/>
    </head>
    <body>
        <!--control buttons--> 
        <section class="header">
            <form method="POST">
                <input type="submit" name="reset" value="Reset Grid" />
            </form>
            <?php $headerFields = $app->getHeaderFields(); ?>
            <p>All words: <span><?php echo $headerFields['allWords']; ?></span></p>
            <p>Placed words: <span><?php echo $headerFields['placedWords']; ?></span></p>
            <p>Omitted words: <span><?php echo $headerFields['omittedWords']; ?></span></p>
        </section>
        
        <!--display crossword-->
        <section class="left">
            <table>
                <tbody>
                    <?php
                    $crossword = $app->getCrossword();
                    foreach ($crossword as $row) {
                        ?>
                        <tr>
                            <?php foreach ($row as $cell) { ?>
                                <td><div><?php echo $cell; ?></div></td>
                        <?php } ?>     
                        </tr>
                    <?php } ?>                
                </tbody>
            </table>
        </section>


        <section class="right">
            <?php $words = $app->getSequenceWords(); 
            foreach ($words as $word) {
                foreach ($word as $key => $value) { ?>
                    <p> <?php echo $key . ' => ' . $value; ?> </p>
                    
                <?php }?>
                    <hr>
            <?php }?>
        </section>

        <script src="https://code.jquery.com/jquery-3.1.0.min.js"   integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s="   crossorigin="anonymous"></script>
        <script src="js/main.js"></script>
    </body>
</html>

