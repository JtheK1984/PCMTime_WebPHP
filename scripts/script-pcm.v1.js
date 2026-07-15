function setupDarkModeToggle() {
  const toggle = document.getElementById('toggleDarkMode');
  if (!toggle) {
    console.warn('Element #toggleDarkMode nicht gefunden.');
    return;
  }

  // Beim Laden prüfen, ob Dark Mode aktiv ist und Checkbox setzen
  if (localStorage.getItem('darkmode') === 'enabled') {
    toggle.checked = true;
    document.body.classList.add('darkmode');
  }

  // Umschaltfunktion für den Dark Mode mit Speichern
  toggle.addEventListener('change', function() {
    if(this.checked) {
      document.body.classList.add('darkmode');
      localStorage.setItem('darkmode', 'enabled');
			
    } else {
      document.body.classList.remove('darkmode');
      localStorage.setItem('darkmode', 'disabled');
    }
  });
}

// Tab-Funktion für Tab-Gruppen ohne Nummer
function openTab(evt, tabName) {
  var i, tabcontent, tablinks;

  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  const tab = document.getElementById(tabName);
  if(tab) {
    tab.style.display = "block";
  }
  evt.currentTarget.className += " active";
}

// Tab-Funktion für Tab-Gruppen mit Nummer (tabcontent1, tablinks1)
function openTab1(evt, tabName) {
  var i, tabcontent, tablinks;

  tabcontent = document.getElementsByClassName("tabcontent1");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  tablinks = document.getElementsByClassName("tablinks1");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  const tab = document.getElementById(tabName);
  if(tab) {
    tab.style.display = "block";
  }
  evt.currentTarget.className += " active";
}
function openTab2(evt, tabName) {
  var i, tabcontent, tablinks;

  tabcontent = document.getElementsByClassName("tabcontent2");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  tablinks = document.getElementsByClassName("tablinks2");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  const tab = document.getElementById(tabName);
  if(tab) {
    tab.style.display = "block";
  }
  evt.currentTarget.className += " active";
}
// DOM ready - Setup ausführen
document.addEventListener("DOMContentLoaded", function(){
  setupDarkModeToggle();

  // Standardmäßig erster Tab bei Gruppe ohne Nummer öffnen (wenn vorhanden)
  const tabs = document.getElementsByClassName("tablinks");
  if(tabs.length > 0) {
    tabs[0].click();
  }

  // Standardmäßig erster Tab bei Gruppe mit Nummer öffnen (wenn vorhanden)
  const tabs1 = document.getElementsByClassName("tablinks1");
  if(tabs1.length > 0) {
    tabs1[0].click();
  }
})

