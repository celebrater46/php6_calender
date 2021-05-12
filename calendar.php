<?php

namespace MyApp;

// クラスを使えるようになるとコードがすっきりする上に、拡張性の高いコードが書けるようになる。
class Calendar {
  public $prev;
  public $next;
  public $yearMonth;
  private $_thisMonth;

  public function __construct() {
    try {
      // t がセットされていない、または日付形式が xxxx-xx でない場合の処理
      if(!isset($_GET["t"]) || !preg_match("/\A\d{4}-\d{2}\z/", $_GET["t"])) {
        // namespace（名前空間）を使う場合、PHPに標準で用意されているException, DateTime などのクラスは一番上位の namespace から呼び出さなければならないため、最初に \ を入れる
        throw new \Exception(); 
      }
      $this->_thisMonth = new \DateTime($_GET["t"]);
    } catch (\Exception $e) {
      $this->_thisMonth = new \DateTime("first day of this month"); // xampp だとこっち出る（?t=1985-01で改変可）
    }
    $this->prev = $this->_createPrevLink(); // プライベートメソッドにはアンダーバーで区別を
    $this->next = $this->_createNextLink();
    $this->yearMonth = $this->_thisMonth->format("F Y");
  }

  private function _createPrevLink() {
    $dt = clone $this->_thisMonth;
    return $dt->modify("-1 month")->format("Y-m");
  }

  private function _createNextLink() {
    $dt = clone $this->_thisMonth;
    return $dt->modify("+1 month")->format("Y-m");
  }

  public function show() {
    $tail = $this->_getTail();
    $body = $this->_getBody();
    $head = $this->_getHead();
    $html = "<tr>" . $tail . $body . $head . "</tr>";
    echo $html; // 忘れてた（）
  }

  // 名前空間とプロパティ（this）を追加、return で値を返す
  private function _getTail() {
    // 先月の日付を追加する処理
    $tail = "";
    $lastDayOfPrevMonth = new \DateTime("last day of " . $this->yearMonth . " -1 month");
    while ($lastDayOfPrevMonth->format("w") < 6) {
      $tail = sprintf(
        "<td class='gray'>%d</td>",
        $lastDayOfPrevMonth->format("d")
      ) . $tail;
      $lastDayOfPrevMonth->sub(new \DateInterval("P1D"));
    }
    return $tail;
  }

  private function _getBody() {
    $body = "";

    // DatePeriod() 特定の機関の日付オブジェクトを作る
    $period = new \DatePeriod(
      // new DateTime("first day of this month"), // 文字列で。割と柔軟に解釈してくれる。
      new \DateTime("first day of " . $this->yearMonth),
      new \DateInterval("P1D"), // 日付間隔を1日ごとに
      // new DateTime("first day of next month") // 終わりの指定（翌月初日は含まない）
      new \DateTime("first day of " . $this->yearMonth . " +1 month") // next month
    );
    
    $today = new \DateTime("today");
    foreach ($period as $day) {
      // 7 の倍数時に行替えタグを挿入
      if ($day->format("w") === "0") {
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
    return $body;
  }

  private function _getHead() {
    $head = "";
    $firstDayOfNextMonth = new \DateTime("first day of " . $this->yearMonth . " +1 month");
    // 翌月1日が日曜日になったら処理を中断するwhile
    while ($firstDayOfNextMonth->format("w") > 0) {
      $head .= sprintf(
        "<td class='gray'>%d</td>",
        $firstDayOfNextMonth->format("d")
      );
      // while 文なので加算処理必須
      $firstDayOfNextMonth->add(new \DateInterval("P1D"));
    }
    return $head;
  }
}

/*
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
*/