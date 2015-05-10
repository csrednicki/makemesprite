<img src="images/makemesprite-logo-small.png">

<ol>
    <li>
        <h3>Co to jest makemesprite ?</h3>
        <p>Makemesprite to program linii komend wykorzystujący interpreter PHP oraz bibliotekę GD2, dzięki któremu w łatwy sposób możesz połączyć wiele małych plików graficznych (np. ikon) w jeden większy obraz.</p>
    </li>

    <li>
        <h3>Po co powstał ten program ?</h3>
        <p>Jest wiele serwisów internetowych które oferują zautomatyzowane przygotowanie pliku sprajtów ale jest bardzo mało programów które oferują to po stronie użytkownika w dodatku z linii komend.<br>Program ten powstał także dlatego, że dostępne rozwiązania ktore istniały sprawiały różne problemy. Potrzebowałem narzędzia które uruchomi się w praktycznie każdym środowisku serwerowym oraz będzie wysoce konfigurowalne.</p>
    </li>

    <li>
        <h3>Do czego może mi się przydać ?</h3>
        <p>Łączenie wielu obrazków w jeden większy służy głównie optymalizacji ilości zapytań do serwera WWW. Zapytania te wykonywane są przez przeglądarki w trakcie ładowania strony internetowej. Mając na stronie 30 ikon o rozmiarze 16x16 pikseli w normalnej sytuacji przeglądarka musi pobrać 30 różnych obrazków. W trakcie pobierania tych obrazków przeglądarka komunikuje się z serwerem i wymienia dane. W każdym takim zapytaniu przesyłane są między innymi dane z ciasteczka (cookie). Jeżeli strona wykorzystuje te ciasteczka do przetrzymywania danych to są one każdorazowo wysyłane do serwera. Taka sytuacja może prowadzić do zwolnienia procesu pobierania danych i nie potrzebnego wykorzystania przepustowości łącza. Dzięki makemesprite przeglądarka musi wykonać tylko jedno zapytanie do serwera, znacznie przyśpieszając czas ładowania strony.</p>
    </li>
    
    <li>
        <h3>Jak wykorzystać ten połączony obrazek ?</h3>
        <p>Makemesprite łącząc obrazki generuje od razu plik CSS dzięki któremu przeglądarka internetowa wie w którym miejscu na stworzonym obrazie znajduje się potrzebna ikona.</p>
    </li>

    <li>
        <h3>Licencja</h3>
        <p>Program udostępniam na licencji MIT (znanej także jako X11). Oznacza to że każda osoba która pobierze makemesprite może go używać, kopiować, modyfikować, wykorzystywać w swoich projektach (w tym komercyjnych).<br>Licencja nie zezwala na usuwanie lub modyfikowanie oryginalnych praw autorskich.</p>
    </li>

    <li>
        <h3>Wymagania programu</h3>
        <p>Do poprawnego działania wymagane jest PHP w wersji 4 oraz biblioteka GD2.<br>Jeżeli będzie wykorzystywana opcja –crush (opisana w punkcie 7.3.2) to dodatkowo potrzebny będzie program <a target="_blank" href="http://pmt.sourceforge.net/pngcrush/">PNG Crush</a></p>
    </li>

    <li>
        <h3>Instalacja</h3>
        <p>Jeżeli w systemie jest już zainstalowane PHP to program nie wymaga specjalnej instalcji.<br>Plik makemesprite.php wystarczy skopiować do wybranego katalogu i uruchomić w sposób opisany w punkcie "Podstawowe użycie programu".</p>
    </li>


    <li>
        <h4>Pliki konfiguracyjne</h4>
        <p>Program korzysta z plików konfiguracyjnych, które wskazują ikony do połączenia.  Podstawowy plik konfiguracyjny składa się z nazw plików wypisanych jeden pod drugim, które maja być połączone. Wskazane pliki graficzne muszą się znajdować w tym samym katalogu co plik konfiguracyjny. W pliku konfiguracyjnym można korzystać z komentarzy, wówczas linia z komentarzem jest poprzedzana średnikiem.</p>
<pre>; To jest komentarz
file1.png
file2.png
file3.png</pre>
        <p>Nieco bardziej zaawansowane wykorzystanie pliku konfiguracyjnego można osiągnąć podając przed nazwą pliku nazwę klasy CSS określającej nazwę nowej ikony. Składnia pliku wygląda wówczas tak:</p>
<pre>; To jest komentarz
.classname1,file1.png
.classname2,file2.png
.classname3,file3.png
</pre>
    </li>

    <li>
        <h4>Podstawowe użycie programu.</h4>
        <p>Program należy wywołać z linii komend.</p>
        <pre>php -f makemesprite.php -- --config sprite.conf --css sprite.css --image sprite.png</pre>
        <p>Po uruchomieniu program wczytuje plik konfiguracyjny sprite.conf. Następnie z katalogu w którym znajduje się plik konfiguracyjny pobierane są pliki graficzne do połączenia. Wynikiem działania programu są dwa nowe pliki: sprite.png i sprite.css</p>
    </li>

    <li>
        <h4>Opcje programu</h4>

        <ol>
            <li>
                <h5>config</h5>
                <pre>--config &lt;ścieżka do pliku konfiguracyjnego&gt;</pre>
                <p>określa położenie pliku konfiguracyjnego. Można używać ścieżek absolutnych, np:</p>
                <p>Przykład:</p>
                <pre>--config d:\webdev\mynewsite\icons\sprite.conf</pre>
            </li>

            <li>
                <h5>crush</h5>
                <pre>--crush &lt;ścieżka do pliku pngcrush.exe&gt;</pre>
                <p>określa czy użyć dodatkowego narzędzia pngcrush.exe aby zoptymalizować wynikowy plik PNG.<br><a target="_blank" href="http://pmt.sourceforge.net/pngcrush/">PNG Crush</a> jest uruchamiany z podstawowymi ustawieniami z opcją -q</p>
                <p>Przykład:</p>
                <pre>--crush c:\programy\pngcrush\pngcrush.exe</pre>
            </li>

            <li>
                <h5>css</h5>
                <pre>--css</pre>
                <p>określa położenie wynikowego pliku CSS. Można używać ścieżek absolutnych. </p>
                <p>Przykład:</p>
                <pre>--css d:\webdev\mynewsite\icons.css</pre>
            </li>

            <li>
                <h5>csspath</h5>
                <pre>--csspath &lt;docelowa ścieżka do wynikowego pliku graficznego&gt;</pre>
                <p>określa czy w pliku CSS ma być podana specjalna ścieżka do pliku graficznego. Ta opcja pozwala na dowolne modyfikacje ścieżki pliku graficznego ze sprajtami.</p>
                <p>Przykład:</p>
                <pre>--csspath /images/gui/buttons.png</pre>
                <p>Wynik (przykładowa linia w pliku css):</p>
                <pre>.sprite{background:url(/images/gui/buttons.png) no-repeat -110px -177px}</pre>
            </li>

            <li>
                <h5>datauri</h5>
                <pre>--datauri</pre>
                <p>opcja ta służy do przekształcenia poszczególnych ikon sprajtów do zakodowanej metodą base64 postaci tekstowej i umieszczenia ikon bezpośrednio w pliku CSS. W tym wypadku plik graficzny w ogóle nie jest generowany. Nie mają też zastosowania opcje do zmiany ułożenia ikon w pliku graficznym. Umieszczenie ikon w stylach CSS umożliwia np. przechowywanie sprajtów bezpośrednio w pliku html (w tagach &lt;style&gt;&lt;/style&gt;) a przez to wyeliminowanie zależności od innych plików. Oczywiście jak wszystko co fajne, to rozwiązanie ma także złe strony. Wykorzystanie datauri powoduje znaczne zwiększenie objętości kodu CSS.</p>
                <p>Wynik (przykładowa linia w pliku css):</p>
                <pre>.sprite{background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB[ ...tu skróciłem tekst... ]BJRU5ErkJggg==) no-repeat 0 0};</pre>
                <p>Następnie w kodzie HTML można normalnie korzystać z klasy sprite aby uzyskać obrazek.</p>
                <pre>&lt;div class="sprite"&gt;&lt;/div&gt;</pre>
                <p>Więcej o metodzie kodowania obrazków do postaci tekstowej (datauri) można znaleźć tutaj na witrynie <a target="_blank" href="http://css-tricks.com/data-uris/">CSS-Tricks</a>.</p>
            </li>

            <li>
                <h5>help</h5>
                <pre>--help</pre>
                <p>pokazuje opis przełączników komendy makemesprite</p>
            </li>

            <li>
                <h5>html</h5>
                <pre>--html</pre>
                <p>włączenie generowania testowego pliku html który można otworzyć w przeglądarce internetowej. Plik ten ułatwia wizualne sprawdzenie poprawności wygenerowanego wynikowego pliku graficznego ze sprajtami.</p>
            </li>

            <li>
                <h5>htmlpath</h5>
                <pre>--htmlpath &lt;docelowa ścieżka do wynikowego pliku css&gt;</pre>
                <p>określa czy w pliku HTML ma być podana specjalna ścieżka do pliku CSS. Ta opcja pozwala na dowolne modyfikacje ścieżki pliku CSS z definicjami sprajtów.</p>
                <p>Przykład:</p>
                <pre>--htmlpath /css/buttons.css</pre>
                <p>Wynik (pierwsza linia w pliku HTML):</p>
                <pre>&lt;link href="/css/buttons.css" rel="stylesheet" type="text/css"&gt;</pre>
            </li>

            <li>
                <h5>image</h5>
                <pre>--image</pre>
                <p>określa położenie wynikowego pliku graficznego na dysku. Można używać ścieżek absolutnych.</p>
                <p>Przykład:</p>
                <pre>--image d:\webdev\mynewsite\icons.png</pre>
            </li>

            <li>
                <h5>optimal</h5>
                <pre>--optimal</pre>
                <p>opcja ta ustawia optymalny sposób ułożonia obiektów w pliku graficznym ze sprajtami. Metoda ta sortuje sprajty według ich szerokości, najszersza ikona ustala wymiary pliku graficznego. Następnie ikony są sortowane według ich wysokości.</p>
            </li>

            <li>
                <h5>padding</h5>
                <pre>--padding &lt;wielkość odstępu pomiędzy sprajtami podana w pikselach&gt;</pre>
                <p>Przykład (sprajty będą posiadały wokół siebie z każdej strony 5 pikseli pustej przestrzeni):</p>
                <pre>--padding 5</pre>
            </li>

            <li>
                <h5>rows</h5>
                <pre>--rows &lt;ilość rzędów&gt;</pre>
                <p>opcja ta włącza tryb ułożenia ikon w rzędy</p>
                <p>Przykład (sprajty zostaną ułożone w pięciu rzędach):</p>
                <pre>--rows 5</pre>
            </li>

            <li>
                <h5>short</h5>
                <pre>--short</pre>
                <p>opcja powoduje włączenie skróconej formy zapisu CSS.<br>W opcji  standardowej każda z klas ma przypisany plik graficzny wraz ze współrzędnymi. W formie skróconej wszystkie klasy mają przypisywany wspólny plik graficzny, a poszczególne klasy definiują jedynie współrzędne położenia sprajta. Przy małej ilości ikon opcja ta nie spowoduje znacznej optymalizacji kodu, jednak przy dużych plikach (&gt;60 sprajtów) różnice w wielkości wygenerowanego kodu CSS dochodzą do 25%.</p>
                <p>Przykład formy standardowej (plik CSS przykładu ma wielkość 228 bajtów):</p>
<pre>
.classname2{background:url(/test/test2/test3/buttons.png) no-repeat 0 0}
.classname1{background:url(/test/test2/test3/buttons.png) no-repeat 0 -76px}
.classname3{background:url(/test/test2/test3/buttons.png) no-repeat -41px -76px}
</pre>
                <p>Przykład formy skróconej (plik CSS przykładu ma wielkość 216 bajtów):</p>
<pre>
.classname2,.classname1,.classname3{background:url(/test/test2/test3/buttons.png) no-repeat 0 0}
.classname2{background-position:0 0}
.classname1{background-position:0 -76px}
.classname3{background-position:-41px -76px}
</pre>
            </li>

            <li>
                <h5>timestamp</h5>
                <pre>--timestamp</pre>
                <p>Opcja ta włącza zapisywanie dokładnej daty wewnątrz pliku ze sprajtami kiedy został on zbudowany. Data (w formacie RRRR-MM-DD GG:MM:SS) jest zapisywana w postaci graficznego napisu na samym dole wygenerowanego pliku. Taka forma oznaczania pliku jest istotna przy projektach w których kod jest budowany automatycznie (z wykorzystaniem narzędzi typu <a target="_blank" href="http://jenkins-ci.org/">Jenkins</a>), pozwala to na weryfikację poprawności i aktualności stworzonego pliku.</p>
            </li>

            <li>
                <h5>verbose</h5>
                <pre>--verbose &lt;stopień dokładności komunikatów 0-3&gt;</pre>
                <p>Opcja kontroluje ilość informacji która jest pokazywana użytkownikowi w trakcie procesu budowania docelowego pliku graficznego. Standardowo stopień dokładności komunikatów jest ustawiony na 3, dzięki czemu użytkownik dostaje tylko ważne informacje.</p>
                <p>
                Stopnie dokładności komunikatów:<br>
                0 – pokazuje tylko najważniejsze komunikaty<br>
                1 – pokazuje także normalne komunikaty<br>
                2 – pokazuje także komunikaty debugowania<br>
                3 – pokazuje wszystkie komunikaty
                </p>
                <p>Przykład:</p>
                <pre>--verbose 3</pre>
            </li>

            <li>
                <h5>wh</h5>
                <pre>--wh</pre>
                <p>włączenie opcji spowoduje umieszczenie informacji o wysokości i szerokości sprajta w stylu CSS.</p>
                <p>Przykład przed włączeniem opcji:</p>
                <pre>.classname1{background:url(/icons.png) no-repeat -64px -177px}</pre>
                <p>Przykład po włączeniu opcji:</p>
                <pre>.classname1{background:url(/icons.png) no-repeat 0 0;width:38px;height:47px}</pre>
            </li>
        </ol>
    </li>
</ol>

