<?php

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

try {
  // t がセットされていない、または日付形式が xxxx-xx でない場合の処理
  if(!isset($_GET["t"]) || !preg_match("/\A\d{4}-\d{2}\z/", $_GET["t"])) {
    throw new Exception();
  }
  $thisMonth = new DateTime($_GET["t"]);
} catch (Exception $e) {
  $thisMonth = new DateTime("first day of this month"); // xampp だとこっち出る（?t=1985-01で改変可）
}
// var_dump($thisMonth);
// exit;
$dt = clone $thisMonth;
$prev = $dt->modify("-1 month")->format("Y-m");
$dt = clone $thisMonth;
$next = $dt->modify("+1 month")->format("Y-m");

// $t = "2020-06";
// $thisMonth = new DateTime($t); // 2020-05-01
$yearMonth = $thisMonth->format("F Y");

// 餞別の日付を追加する処理
$tail = "";
// $lastDayOfPrevMonth = new DateTime("last day of previous month");
$lastDayOfPrevMonth = new DateTime("last day of " . $yearMonth . " -1 month");
while ($lastDayOfPrevMonth->format("w") < 6) {
  $tail = sprintf(
    "<td class='gray'>%d</td>",
    $lastDayOfPrevMonth->format("d")
  ) . $tail;
  $lastDayOfPrevMonth->sub(new DateInterval("P1D"));
}

$body = "";

// DatePeriod() 特定の機関の日付オブジェクトを作る
$period = new DatePeriod(
  // new DateTime("first day of this month"), // 文字列で。割と柔軟に解釈してくれる。
  new DateTime("first day of " . $yearMonth),
  new DateInterval("P1D"), // 日付間隔を1日ごとに
  // new DateTime("first day of next month") // 終わりの指定（翌月初日は含まない）
  new DateTime("first day of " . $yearMonth . " +1 month") // next month
);

$today = new DateTime("today");
foreach ($period as $day) {
  // 7 の倍数時に行替えタグを挿入
  if ($day->format("w") % 7 === 0) {
    $body .= "</tr><tr>";
  }
  $todayClass = ($day->format("Y-m-d") === $today->format("Y-m-d")) ? "today" : "";

  // format() は 変数の値を任意の書式で表示する。「d = 01 to 31, j = 1 to 31, w = 曜日 0 to 6, m = 月 1 to 12, M = 月 Jan to Dec, 詳しくは[https://www.php.net/manual/ja/function.date.php]
  $body .= sprintf(
    // "<td class='youbi_%d'>%d</td>", [%s] が抜けていたため、カレンダーの数字が全て 0 になってしまった。%s は文字列指定のはずだけど、これがないと何で0になるのか……%d が足りないならまだわかるんだけど。
    "<td class='youbi_%d %s'>%d</td>",
    $day->format("w"),
    $todayClass, $day->format("d") // JS と違って最後にカンマ入れるとバグる
  );
}

// $firstDayOfNextMonth = new DateTime("first day of next month");
$firstDayOfNextMonth = new DateTime("first day of " . $yearMonth . " +1 month");

$head = "";
// 翌月1日が日曜日になったら処理を中断するwhile
while ($firstDayOfNextMonth->format("w") > 0) {
  $head .= sprintf(
    "<td class='gray'>%d</td>",
    $firstDayOfNextMonth->format("d")
  );
  // while 文なので加算処理必須
  $firstDayOfNextMonth->add(new DateInterval("P1D"));
}

$html = "<tr>" . $tail . $body . $head . "</tr>";

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
        <th><a href="/?t=<?php echo h($prev); ?>">&laquo;</a></th>
        <th colspan="5"><?php echo h($yearMonth); ?></th>
        <th><a href="/?t=<?php echo h($next); ?>">&raquo;</a></th>
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
      <?php echo $html; ?>
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