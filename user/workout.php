<?php
include "../templates/func.php";
include "../templates/settings.php";

$user_data->set_program($conn);
$weekday = date("N") - 1;

if (isset($_POST["workout_to_fav"])){
    $user_data->change_featured_workouts($conn, $_POST['workout_to_fav']);
}
?>
<!DOCTYPE html>
<html lang="en">
<?php inc_head(); ?>
<body>
    <?php include "../templates/header.php" ?>

    <main class="workouts-block">
        <div class="container">
            <?php if ($user_data->program->get_id() > 0){ ?>
            <!-- Day's workout swiper -->
            <swiper-container class="workouts-swiper" navigation="true">
                <swiper-slide class="workouts-slide">
                <!-- Slide -->
                <?php
                    $workout = new Workout($conn, $user_data->program->program[$weekday], $weekday);
                    if ($workout->holiday){
                        include "../templates/holiday.html";
                    }else{ $workout->set_muscles(); ?>
                        <!-- slide(no arrows) -->
                        <section class="workouts-card">
                            <!-- Title and button to add to favorite collection -->
                            <form method="post" class="workouts-card__header">
                                <h2 class="workouts-card__date"><?php echo date("d.m.Y"); ?></h2>
                                <input type="hidden" name="workout_to_fav" value="<?php echo $workout->get_id(); ?>">
                                <button type="submit" class="workouts-card__favorite-btn">
                                    <?php if (array_search((string)$workout->get_id(), $user_data->featured_workouts)){ // fix ?>
                                        <img src="../img/favorite_added.svg" alt="">
                                    <?php }else{ ?>
                                        <img src="../img/favorite.svg" alt="">
                                    <?php } ?>
                                </button>
                            </form>
                            <!-- Content of workout -->
                            <section class="workouts-card__content">
                                <!-- Exercises array -->
                                <section class="workouts-card__exercises-cover">
                                    <!-- Exercise items -->
                                    <?php $workout->print_exercises($conn); ?>
                                </section>
                                <!-- Info about day workout -->
                                <?php
                                $date1 = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
                                $date2 = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
                                $sql = "SELECT id FROM workout_history WHERE date_completed > $date1 AND date_completed < $date2 AND user=".$user_data->get_id();
                                if ($result = $conn->query($sql)){
                                    if ($result->num_rows == 0){
                                        $workout->print_workout_info(2, $user_data->get_id(), 1);
                                    }else{
                                        $workout->print_workout_info(2, $user_data->get_id());
                                    }
                                }else{
                                    $workout->print_workout_info(2, $user_data->get_id());
                                }
                                ?>
                            </section>
                        </section>
                    <?php } ?>
                </swiper-slide>
                <swiper-slide class="workouts-slide">
                    <?php
                    if ($weekday == 6){
                        $weekday = 0;
                    }else{
                        $weekday++;
                    }
                    $workout = new Workout($conn, $user_data->program->program[$weekday], $weekday);
                    if ($workout->holiday){
                        include "../templates/holiday.html";
                    }else{ $workout->set_muscles(); ?>
                    <section class="workouts-card">
                        <div class="workouts-card__header">
                            <h2 class="workouts-card__date"><?php echo date("d.m.Y", time() + 86400); ?></h2>
                            <input type="hidden" name="workout_to_fav" value="<?php echo $workout->get_id(); ?>">
                            <button type="submit" class="workouts-card__favorite-btn">
                                <?php if (array_search((string)$workout->get_id(), $user_data->featured_workouts)){ ?>
                                    <img src="../img/favorite_added.svg" alt="">
                                <?php }else{ ?>
                                    <img src="../img/favorite.svg" alt="">
                                <?php } ?>
                            </button>
                        </div>
                        <section class="workouts-card__content">
                            <section class="workouts-card__exercises-cover">
                                <?php $workout->print_exercises($conn); ?>
                            </section>
                            <?php $workout->print_workout_info(0, $user_data->get_id()); ?>
                        </section>
                    </section>
                    <?php } ?>
                </swiper-slide>
            </swiper-container>
            <?php } else { ?>
                <div class="workouts-card__no-program">
                    <p class="workouts-card__no-program-title">Нет программы</p>
                </div>
            <?php } ?>
            <section class="workout-other">
                <!-- Friends' workouts -->
                <section class="friends-block">
                    <!-- Title and button to search friends -->
                    <div class="friends-block__header">
                        <h1 class="friends-block__header-title">Тренировки друзей</h1>
                        <a href="search_users.php" class="friends-block__header-button" href=""><img src="../img/search.svg" alt=""></a>
                    </div>
                    <!-- Friends' workout swiper -->
                    <swiper-container class="friends-block__swiper" navigation="true">
                        <?php
                        $user_data->set_subscriptions($conn);
                        print_user_list($conn, $user_data->subscriptions);
                        ?>
                      </swiper-container>
                </section>
                <?php $user_data->print_workout_history($conn); ?>
                <!-- Buttons favorite workouts and my program -->
                <section class="workout-other__buttons">
                    <!-- <a class="button-text workout-other__button" href=""><p>Избранное</p> <img src="../img/favorite_white.svg" alt=""></a> -->
                    <a class="button-text workout-other__button" href="my_program.php"><p>Моя программа</p> <img src="../img/my_programm.svg" alt=""></a>
                </section>
            </section>
        </div>
    </main>

    <?php include "../templates/footer.html" ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-element-bundle.min.js"></script>
    <script>
        // Button to see exercise info
        let infoExerciseButton = document.querySelectorAll('.exercise-item__info-btn');
        let closeInfoExerciseButton = document.querySelectorAll('.exercise-item__info-close');
        let infoBlock = document.querySelectorAll('.exercise-item__info-content');

        for(let i = 0; i < infoExerciseButton.length; i++){
            infoExerciseButton[i].addEventListener('click', function(){
                infoBlock[i].style.cssText = `top: -1%;`;
            });
        }
        for(let i = 0; i < closeInfoExerciseButton.length; i++){
            closeInfoExerciseButton[i].addEventListener('click', function(){
                infoBlock[i].style.cssText = `top: -101%;`;
            });
        }


        // Info slide items' spans width
        let infoItemsSpans = document.querySelectorAll('.workouts-card__info-item span');
        let maxSpanWidth = 0;

        for(let i = 0; i < infoItemsSpans.length; i++){
            maxSpanWidth = Math.max(maxSpanWidth, infoItemsSpans[i].clientWidth);
        }

        for(let i = 0; i < infoItemsSpans.length; i++){
            infoItemsSpans[i].style.cssText = `width: ${maxSpanWidth}px;`;
            console.log(infoItemsSpans[i].clientWidth)
        }
    </script>
</body>
</html>