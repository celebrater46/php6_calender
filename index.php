<?php

require "calendar.php";

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

$cal = new \MyApp\Calendar(); // new 忘れてたよ

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>カレンダー</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <table>
    <thead>
      <tr>
        <!-- 前月を表示。出力時にはエスケープしたいので h という関数を使う -->
        <!-- Lesson 12 から別ファイルに移したため、作成したインスタンス $cal で呼び出し。 -->
        <th><a href="/?t=<?php echo h($cal->prev); ?>">&laquo;</a></th>
        <th colspan="5"><?php echo h($cal->yearMonth); ?></th>
        <th><a href="/?t=<?php echo h($cal->next); ?>">&raquo;</a></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>SUN</td>
        <td>MON</td>
        <td>TUE</td>
        <td>WED</td>
        <td>THU</td>
        <td>FRI</td>
        <td>SAT</td>
      </tr>
      <?php echo $cal->show(); ?>
      <!-- <tr>
        <td class="youbi-0">1</td>
        <td class="youbi-1">2</td>
        <td class="youbi-2">3</td>
        <td class="youbi-3">4</td>
        <td class="youbi-4 today">5</td>
        <td class="youbi-5">6</td>
        <td class="youbi-6">7</td>
      </tr>
      <tr>
        <td class="youbi-0">30</td>
        <td class="youbi-1">31</td>
        <td class="gray">1</td>
        <td class="gray">2</td>
        <td class="gray">3</td>
        <td class="gray">4</td>
        <td class="gray">5</td>
      </tr> -->
    </tbody>
    <tfoot>
      <tr>
        <th colspan="7"><a href="/">Today</a></th>
      </tr>
    </tfoot>
  </table>
</body>
</html>