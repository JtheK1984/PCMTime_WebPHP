<nav class="navbar">
  <ul class="nav-links" id="nav-links">
    <li><a href="/time/index.php">Übersicht</a></li>
    <li><a href="/time/booking_online.php">Onlinebuchung</a></li>
    <li><a href="/time/booking_offline.php">Buchungsübersicht</a></li>
    <li><a href="/time/absenceview.php">Fehltage</a></li>
    <li><a href="/time/monthview.php">Monatswerte</a></li>
    <li><a href="blank">Reports</a></li>
    <li><a href="logout.php">Abmelden</a></li>
  </ul>
  <div class="burger" id="burger" aria-label="Menü umschalten" role="button" tabindex="0">
    <div></div>
    <div></div>
    <div></div>
  </div>
</nav>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const burger = document.getElementById('burger');
    const navLinks = document.getElementById('nav-links');
    burger.addEventListener('click', function() {
      navLinks.classList.toggle('active');
      burger.classList.toggle('active');
    });
  });
</script>