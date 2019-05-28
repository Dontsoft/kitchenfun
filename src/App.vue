<template>
  <div id="app">
    <div class="section padded notification-container">
      <div class="card notification row padding xs" v-for="n in notifications" v-bind:key="n.id" v-bind:class="n.type == -1 ? 'error': 'message'">
        <span class="text">{{ n.text }}</span>
        <span class="close" @click="remove_notification(n.id)"></span>
      </div>
    </div>
    <nav class="section flex row v-center h-center"><h1>SWING.kitchenfun</h1></nav>
    <main class="section full-height column padded">
      <div class="card padding xs column" ref="initial-card">
        <h2>1. Datei hochladen</h2>
        <div class="dropzone">
          <dontsoft-dropzone @error="show_error" @handle="handle_file"></dontsoft-dropzone>
        </div>
      </div>
      <div class="card padding xs column" v-bind:class="current_state >= 1 ? '' : 'disabled'" ref="column-card">
        <h2 class="with-help">2. Spalten auswählen<span class="help" @click="toggle_column_help"></span></h2>
        <small v-if="show_column_help">Damit alles wie gewollt funktioniert und die Einbindung späterer SWING.kitchenfuns einfacher ist müssen in diesem Schritt die Spalten aus der Umfrage in meine Datenstruktur übertragen werden. Derzeit ist alles relativ stark an den aktuellen SWING.kitchenfun angepasst, dies wird sich später aber noch ändern. Da ich das hier in 2 Tagen zusammengeklimpert habe, ist mir leider nichts anderes eingefallen.</small>
        <div class="column" v-if="current_state >= 1">
          <div class="row v-center column-selector">
            <span class="label"><strong>Spalte für Teamname</strong></span>
            <div class="dropdown-container">
              <dontsoft-dropdown v-bind:elements="columns_for_dropdown" @changed="select_teamname_column"></dontsoft-dropdown>
            </div>
          </div>
          <div class="row v-center column-selector">
            <span class="label"><strong>Spalte für Team Mitglied 1</strong></span>
            <div class="dropdown-container">
              <dontsoft-dropdown v-bind:elements="columns_for_dropdown" @changed="select_member_1_column"></dontsoft-dropdown>
            </div>
          </div>
          <div class="row v-center column-selector">
            <span class="label"><strong>Spalte für Team Mitglied 2</strong></span>
            <div class="dropdown-container">
              <dontsoft-dropdown v-bind:elements="columns_for_dropdown" @changed="select_member_2_column"></dontsoft-dropdown>
            </div>
          </div>
          <div class="row v-center column-selector">
            <span class="label"><strong>Spalte für E-Mail</strong></span>
            <div class="dropdown-container">
              <dontsoft-dropdown v-bind:elements="columns_for_dropdown" @changed="select_email_column"></dontsoft-dropdown>
            </div>
          </div>
          <div class="row v-center column-selector">
            <span class="label"><strong>Spalte für Adresse</strong></span>
            <div class="dropdown-container">
              <dontsoft-dropdown v-bind:elements="columns_for_dropdown" @changed="select_addresse_column"></dontsoft-dropdown>
            </div>
          </div>
          <div class="row v-center column-selector">
            <span class="label"><strong>Spalte für Adresseanmerkungen</strong></span>
            <div class="dropdown-container">
              <dontsoft-dropdown v-bind:elements="columns_for_dropdown" @changed="select_addresse_notes_column"></dontsoft-dropdown>
            </div>
          </div>
          <div class="row v-center column-selector">
            <span class="label"><strong>Spalte für Telefon</strong></span>
            <div class="dropdown-container">
              <dontsoft-dropdown v-bind:elements="columns_for_dropdown" @changed="select_phone_column"></dontsoft-dropdown>
            </div>
          </div>
          <div class="row v-center column-selector">
            <span class="label"><strong>Spalte für Diät (Vegetarisch, ...)</strong></span>
            <div class="dropdown-container">
              <dontsoft-dropdown v-bind:elements="columns_for_dropdown" @changed="select_diet_column"></dontsoft-dropdown>
            </div>
          </div>
          <div class="row v-center column-selector">
            <span class="label"><strong>Spalte für Essensanmerkungen</strong></span>
            <div class="dropdown-container">
              <dontsoft-dropdown v-bind:elements="columns_for_dropdown" @changed="select_food_notes_column"></dontsoft-dropdown>
            </div>
          </div>
          <div class="row v-center h-center" style="width: 100%" v-if="columns_selected && current_state < 2">
            <span class="btn padding xs" @click="fetch_coords" >Weiter</span>
          </div>
        </div>
      </div>
      <div class="card padding xs column" v-bind:class="current_state >= 2 ? '' : 'disabled'" ref="geo-card">
        <h2>3. Geokoordinaten holen</h2>
        <small>Aus technischen Gründen wird hier die Luftlinie genutzt. Die eigenlichen Abstände können (und werden) von den ausgerechneten abweichen.</small>
        <div class="" v-if="current_state >= 2">
          <div class="column">
            <div class="geo-row row">
              <div class="geo-name"><strong>Name</strong></div>
              <div class="geo-addresse"><strong>Adresse</strong></div>
              <div class="geo-club-distance"><strong>Clubentfernung</strong></div>
            </div>
            <div class="geo-row row" v-for="data in geodata" v-bind:key="data.id">
              <div class="geo-addresse">{{data.name}}</div>
              <div class="geo-addresse">{{data.addresse}}</div>
              <div class="geo-club-distance">{{data.club_distance.toFixed(2)}} km</div>
            </div>
          </div>
          <div class="row v-center h-center" style="width: 100%" v-if="fetching_index == registrant_data.length && current_state < 3">
            <span class="btn padding xs" @click="calculate_pairings">Weiter</span>
          </div>
        </div>
      </div>
      <div class="card padding xs column" v-bind:class="current_state >= 3 ? '' : 'disabled'" ref="pairing-card">
        <h2>4. Paarungen berechnen</h2>
        <small>Der <em>Gastgeber</em> ist <em>kursiv</em>. Die Gästeteams sind normal formatiert.</small>
        <div class="" v-if="current_state >= 3">
          <div class="column">
            <div class="row pairing-row">
              <div class="pairing-starter"><strong>Vorspeise</strong></div>
              <div class="pairing-main"><strong>Hauptspeise</strong></div>
              <div class="pairing-dessert"><strong>Nachspeise</strong></div>
            </div>
            <div class="row pairing-row pairing-entry-row" v-for="course in course_map" v-bind:key="course.index">
              <div class="pairing-starter">
                <em>{{course.starter["host"]["name"]}}</em><br>
                {{course.starter["guest_1"]["name"]}}<br>
                {{course.starter["guest_2"]["name"]}}
              </div>
              <div class="pairing-main">
                <em>{{course.main["host"]["name"]}}</em><br>
                {{course.main["guest_1"]["name"]}}<br>
                {{course.main["guest_2"]["name"]}}
              </div>
              <div class="pairing-dessert">
                <em>{{course.dessert["host"]["name"]}}</em><br>
                {{course.dessert["guest_1"]["name"]}}<br>
                {{course.dessert["guest_2"]["name"]}}
              </div>
            </div>
          </div>
          <div class="row v-center h-center" style="width: 100%" v-if="current_state < 4">
            <span class="btn padding xs" @click="next_state">Weiter</span>
          </div>
        </div>
      </div>
      <div class="card padding xs column" v-bind:class="current_state >= 4 ? '' : 'disabled'" ref="email-card">
        <h2>5. E-Mails erstellen</h2>
        <div class="" v-if="current_state >= 4">
          <div class="column">
            <div class="mail-overview-row row" v-for="data in mapped_registrant_data" v-bind:key="data.id">
              <span class="name">{{data.name}}</span>
              <input type="email" v-model="data.email">
            </div>
          </div>
          <div class="column organizer-column">
            <label for="organizer">Organisator(en)</label>
            <small>Wenn es mehrere Organisatoren gibt, einfach mit <strong>und</strong> trennen</small>
            <input type="text" id="organizer" v-model="organizer" autocomplete="off">
          </div>
          <div class="column organizer-column checkmark">
            <input type="checkbox" id="org-email" v-model="org_email">
            <label for="org-email">Alle E-Mails an den/die Organisator(en) verschicken</label>
          </div>
          <div class="column organizer-column" v-if="org_email">
            <label for="organizer-1-email">Organisatoren E-Mails (max. 2)</label>
            <small>{{org_email ? "Beide E-Mails bekommen zur Bestätigung Kopien aller E-Mails zugesandt, diese werden auch als Antwortemails gesetzt." : "Die Organisatoren bekommen keine Kopien der versandten E-Mails. Ihre Adressen werden nur als Antwortemails gesetzt."}}</small>
            <input type="email" id="organizer-1-email" v-model="organizer_1_email" autocomplete="off">
            <input type="email" id="organizer-2-email" v-model="organizer_2_email" autocomplete="off">
          </div>
          <div class="mail-content">
            <textarea id="mail-content-input" ref="mail-content-input" rows=12 v-model="mail_text"></textarea>
          </div>
          <div class="column preview-column">
            <h3>Vorschau</h3>
            <p>Hallo Team {{mapped_registrant_data[0]["name"]}},</p>
            <p v-html="parsed_mail_text"></p>
            <p v-html="preview_first_team"></p>
            <p>Viele Grüße<br>{{organizer}}</p>
          </div>
          <div class="row v-center h-center" style="width: 100%" v-if="current_state < 5">
            <span class="btn padding xs" @click="send_mails">Senden</span>
          </div>
        </div>
      </div>
      <div class="card padding xs column" v-bind:class="current_state >= 5 ? '' : 'disabled'" ref="email-sending-card">
        <h2>E-Mails versenden</h2>
        <div class="column">
          <div class="mailing-status row v-center h-center" v-for="state in mail_state" v-bind:key="state.index" v-bind:class="state.success == 1 ? 'success' : (state.success == -1 ? 'error' : '')">
            <span class="mail">{{state["mail"]}}</span>
            <span class="state">{{state.success == 1 ? 'Erfolgreich versandt' : (state.success == -1 ? 'Fehler aufgetreten' : 'Sende gerade...')}}</span>
          </div>
        </div>
      </div>
    </main>
    <footer class="section padded row v-center h-center">
      <small class="footer-content row v-center h-center">
        Made with &nbsp;<span class="heart"></span>&nbsp; and &nbsp;<span class="coffee"></span>&nbsp; by &nbsp;<a href="https://dontsoft.com" target="_blank" class="dontsoft-link">Artem Dontsov</a>
      </small>
    </footer>
  </div>
</template>

<script>

import Axios from 'axios';
const parse = require('csv-parse/lib/sync');
const uuidv4 = require('uuid/v4');
const States = Object.freeze({
  INITIAL: 0,
  SELECT_COLUMN: 1,
  GEODATA: 2,
  PAIRINGS: 3,
  EMAILS: 4,
  FINISHED: 5
});

const DIET_FLAGS = Object.freeze({
  VEGETARIAN: 0x01,
  VEGAN: 0x02,
  NO_PORK: 0x04
});

const CLUB_COORDS = {
  "latitude": 50.682736,
  "longitude": 10.932250
};

if (typeof(Number.prototype.toRad) === "undefined") {
  Number.prototype.toRad = function() {
    return this * Math.PI / 180;
  }
}

if (typeof(Array.prototype.shuffle) === "undefined") {
  Array.prototype.shuffle = function() {
    return this.sort(() => Math.random() - 0.5);
  }
}

function b64EncodeUnicode(str) {
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
        return String.fromCharCode('0x' + p1);
    }));
}

export default {
  data: () => {
    return {
      current_state: States.INITIAL,
      notifications: [],
      show_column_help: true,
      columns: [],
      registrant_data: [],
      mapped_registrant_data: [],
      teamname_column: null,
      member_1_column: null,
      member_2_column: null,
      email_column: null,
      addresse_column: null,
      addresse_notes_column: null,
      phone_column: null,
      diet_column: null,
      food_notes_column: null,
      fetching_index: -1,
      geodata: [],
      inter_team_distance_map: [],
      starter: [],
      main: [],
      dessert: [],
      course_map: [],
      mail_text: "",
      organizer: "",
      organizer_1_email: "",
      organizer_2_email: "",
      org_email: true,
      mail_state: []
    }
  },
  computed: {
    parsed_mail_text: function() {
      return this.mail_text.replace(/\n/g,"<br>")
    },
    preview_first_team: function() {
      return this.generate_preview_for_team(0);
    },
    columns_selected: function() {
      return (this.teamname_column != null && this.member_1_column != null && this.member_2_column != null && this.email_column != null && this.addresse_column != null && this.addresse_notes_column != null && this.phone_column != null && this.diet_column != null && this.food_notes_column != null);
    },
    columns_for_dropdown: function() {
      var id = -1;
      return this.columns.map((e) => {
        id++;
        return {
          "label": e,
          "index": id
        };
      });
    }
  },
  methods: {
    try_email_sending: function(index) {
      var team_data = this.mapped_registrant_data[index];
      var data = this.filter_entries(team_data["id"]);
      var starter_string = this.generate_preview(data, "starter");
      var main_string = this.generate_preview(data, "main");
      var dessert_string = this.generate_preview(data, "dessert");
      var organizer_mails = function(mail1, mail2) {
        var arr = [];
        if (mail1.length > 0) {
          arr.push(mail1);
        }
        if (mail2.length > 0) {
          arr.push(mail2);
        }
        return arr;
      };
      var mail_data = {
        "receiver": {
          "mail": team_data["email"],
          "name": team_data["name"]
        },
        "send_to_organizer": this.org_email && organizer_mails(this.organizer_1_email, this.organizer_2_email).length > 0,
        "organizer": organizer_mails(this.organizer_1_email, this.organizer_2_email),
        "title": b64EncodeUnicode("Hallo Team " + team_data["name"]),
        "pretext": b64EncodeUnicode(this.parsed_mail_text),
        "starter": b64EncodeUnicode(this.starter_string),
        "main": b64EncodeUnicode(this.main_string),
        "dessert": b64EncodeUnicode(this.dessert_string),
        "endtext": b64EncodeUnicode("Viele Grüße<br>" + this.organizer)
      }

      var fd = new FormData();
      fd.append("data", JSON.stringify(mail_data));
      Axios.post("/mail.php", fd,  { headers: {'Content-Type': 'multipart/form-data' }}).then((r) => {
        this.mail_state[index].success = 1;
      }).catch((e) => {
        console.log(e.request);
        this.mail_state[index].success = -1;
      });
    },
    generate_preview_for_team: function(index) {
      var team_data = this.mapped_registrant_data[index];
      var data = this.filter_entries(team_data["id"]);
      var starter_string = this.generate_preview(data, "starter");
      var main_string = this.generate_preview(data, "main");
      var dessert_string = this.generate_preview(data, "dessert");
      return "<strong>Vorspeise</strong><br>" + starter_string + "<br>-----------------------<br>" + "<strong>Hauptspeise</strong><br>" + main_string + "<br>-----------------------<br>" + "<strong>Nachspeise</strong><br>" + dessert_string;
    },
    generate_preview: function(data, key) {
      var _d = data[key];
      if (_d["host"]) {
        var preview_string = "<strong>Du bist Gastgeber</strong><br><br><em>Deine Gäste:</em><br>";
        preview_string += _d["data"]["guest_1"]["name"] + " (" + _d["data"]["guest_1"]["member_1"] + ", " + _d["data"]["guest_1"]["member_2"] + ")<br>"
        preview_string += _d["data"]["guest_2"]["name"] + " (" + _d["data"]["guest_2"]["member_1"] + ", " + _d["data"]["guest_2"]["member_2"] + ")<br><br><em>Einschränkungen:</em><br>";
        preview_string += _d["data"]["diet"] + "<br><br><em>Anmerkungen:</em>"
        preview_string += _d["data"]["notes"].join("<br>")
        return preview_string;
      }
      var preview_string = "<em>Gastgeber:</em><br>" + _d["data"]["host"]["name"] + " (" + _d["data"]["host"]["member_1"] + ", " + _d["data"]["host"]["member_2"] + ")<br>" + _d["data"]["addresse"] + "(" + _d["data"]["addresse_notes"] + ")<br>" + _d["data"]["phone"] + "<br><br><em>Gäste:</em><br>";
      preview_string += _d["data"]["guest_1"]["name"] + " (" + _d["data"]["guest_1"]["member_1"] + ", " + _d["data"]["guest_1"]["member_2"] + ")<br>"
      preview_string += _d["data"]["guest_2"]["name"] + " (" + _d["data"]["guest_2"]["member_1"] + ", " + _d["data"]["guest_2"]["member_2"] + ")<br><br><em>Einschränkungen:</em><br>";
      preview_string += _d["data"]["diet"] + "<br><br><em>Anmerkungen:</em>"
      preview_string += _d["data"]["notes"].join("<br>")
      return preview_string;
    },
    filter_entries: function(id) {
      var ret = {
        "starter": {
          "host": true,
          "data": null,
        },
        "main": {
          "host": true,
          "data": null,
        },
        "dessert": {
          "host": true,
          "data": null,
        }
      }

      var diet_to_string = function(diet1, diet2, diet3) {
        var diets = []
        if (diet1 & DIET_FLAGS.VEGETARIAN || diet2 & DIET_FLAGS.VEGETARIAN || diet3 & DIET_FLAGS.VEGETARIAN) {
          diets.push("Vegetarisch")
        }
        if (diet1 & DIET_FLAGS.VEGAN || diet2 & DIET_FLAGS.VEGAN || diet3 & DIET_FLAGS.VEGAN) {
          diets.push("Vegan")
        }
        if (diet1 & DIET_FLAGS.NO_PORK || diet2 & DIET_FLAGS.NO_PORK || diet3 & DIET_FLAGS.NO_PORK) {
          diets.push("Ohne Schweinefleisch")
        }
        return diets.join(", ");
      };

      var _this = this;
      var select = function(_id, type, element, mapped_registrant_data) {
        if (element[type]["host"]["id"] == _id || element[type]["guest_1"]["id"] == _id || element[type]["guest_2"]["id"] == _id) {
          var host = mapped_registrant_data.find((e) => {
            return e["id"] == element[type]["host"]["id"]
          });
          var guest_1 = mapped_registrant_data.find((e) => {
            return e["id"] == element[type]["guest_1"]["id"];
          });
          var guest_2 = mapped_registrant_data.find((e) => {
            return e["id"] == element[type]["guest_2"]["id"]
          });
          return {
              "host": element[type]["host"]["id"] == _id,
              "data": {
              "guest_1": {
                "name": guest_1["name"],
                "member_1": guest_1["member_1"],
                "member_2": guest_1["member_2"]
              },
              "guest_2": {
                "name": guest_2["name"],
                "member_1": guest_2["member_1"],
                "member_2": guest_2["member_2"]
              },
              "host": {
                "name": host["name"],
                "member_1": host["member_1"],
                "member_2": host["member_2"]
              },
              "diet": diet_to_string(host["diet"], guest_1["diet"], guest_2["diet"]),
              "notes" : [host["food_notes"], guest_1["food_notes"], guest_2["food_notes"]],
              "addresse": host["addresse"],
              "addresse_notes": host["addresse_notes"],
              "phone": host["phone"],
            }
          };
        }
        return false;
      }
      for (var i = 0; i < this.course_map.length; i++) {
        var e = this.course_map[i];
        var _starter = select(id, "starter", e, this.mapped_registrant_data);
        var _main = select(id, "main", e, this.mapped_registrant_data);
        var _dessert = select(id, "dessert", e, this.mapped_registrant_data);
        if (_starter != false) {
          ret["starter"] = _starter;
        }
        if (_main != false) {
          ret["main"] = _main;
        }
        if (_dessert != false) {
          ret["dessert"] = _dessert;
        }
        if (ret["starter"]["data"] != null && ret["main"]["data"] != null && ret["dessert"]["data"] != null) {
          break;
        }
      }
      return ret;
    },
    send_mails: function() {
      for (var i = 0; i < this.mapped_registrant_data.length; i++) {
        this.mail_state.push({
          "index": i,
          "mail": this.mapped_registrant_data[i]["email"],
          "success": 0
        });
      }
      for (var i = 0; i < this.mail_state.length; i++) {
        this.try_email_sending(i);
      }
      this.next_state();
    },
    calculate_pairings: function() {
      var _this = this;

      this.next_state();
      var _geodata = this.geodata;
      const M = _geodata.length / 3;
      _geodata.sort((a, b) => {
        return a.club_distance > b.club_distance ? -1 : 1;
      });
      this.starter = [];
      this.main = [];
      this.dessert = [];
      for (var j = 0; j < M; j++) {
        this.starter.push(_geodata[j]);
      }

      for (j = 0; j < M; j++) {
        this.main.push(_geodata[M + j]);
      }

      for (j = 0; j < M; j++) {
        this.dessert.push(_geodata[2 * M + j]);
      }
      this.starter.shuffle();
      this.main.shuffle();
      this.dessert.shuffle();

      var starter_pairings = [];
      var main_pairings = [];
      var dessert_pairings = [];

      var normalize = function(i, M) {
        if (i >= 2 * M) {
          return i - 2*M;
        } else if (i >= M) {
          return i - M;
        }
        return i;
      }

      for (var i = 0; i < M; i++) {


        var starter_guest_1 = 0;
        var starter_guest_2 = 0;

        if (i == 0) {
          starter_guest_1 = 2 * M - 1;
        } else {
          starter_guest_1 = i + M - 1;
        }

        if (i <= 1) {
          starter_guest_2 = 3 * M - 2 + i;
        } else {
          starter_guest_2 = i + 2 * M - 2;
        }

        var main_i = i + M;

        var dessert_i = i + 2 * M;
        var dessert_guest_1 = 0;
        var dessert_guest_2 = 0;

        if (dessert_i == 3*M - 1) {
          dessert_guest_1 = 0;
          dessert_guest_2 = M + 1;
        } else if (dessert_i == 3*M - 2) {
          dessert_guest_1 = dessert_i - 2 * M + 1;
          dessert_guest_2 = M;
        } else {
          dessert_guest_1 = dessert_i - 2 * M + 1;
          dessert_guest_2 = dessert_i - M + 2;
        }


        starter_pairings.push({
          "host": i,
          "guest_1": starter_guest_1,
          "guest_2": starter_guest_2
        });
        main_pairings.push({
          "host": main_i,
          "guest_1": main_i - M,
          "guest_2": main_i + M
        });
        dessert_pairings.push({
          "host": dessert_i,
          "guest_1": dessert_guest_1,
          "guest_2": dessert_guest_2
        });

        this.course_map.push({
          "index": i,
          "starter": {
            "host": this.starter[normalize(i, M)],
            "guest_1": this.main[normalize(starter_guest_1, M)],
            "guest_2": this.dessert[normalize(starter_guest_2, M)]
          },
          "main": {
            "host": this.main[normalize(main_i, M)],
            "guest_1": this.dessert[normalize(main_i - M, M)],
            "guest_2": this.starter[normalize(main_i + M, M)]
          },
          "dessert": {
            "host": this.dessert[normalize(dessert_i, M)],
            "guest_1": this.starter[normalize(dessert_guest_1, M)],
            "guest_2": this.main[normalize(dessert_guest_2, M)]
          },
        });
      }

    },
    calculate_distance: function(lat1, lon1, lat2, lon2) {
      var R = 6371; // km 
      var x1 = lat2 - lat1;
      var dLat = x1.toRad()
      var x2 = lon2-lon1;
      var dLon = x2.toRad();  
      var a = Math.sin(dLat/2) * Math.sin(dLat/2) + 
                    Math.cos(lat1.toRad()) * Math.cos(lat2.toRad()) * 
                    Math.sin(dLon/2) * Math.sin(dLon/2);  
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
      return R * c; 
    },
    fetch_loop: async function() {
      var addresses = this.mapped_registrant_data;
      for (this.fetching_index = 0; this.fetching_index < addresses.length; this.fetching_index++) {
        var _data = new FormData();
        _data.append("addresse", addresses[this.fetching_index]["addresse"]);

        var data = await Axios.post('/geocode.php', _data, { headers: {'Content-Type': 'multipart/form-data' }});
        var coordinates = data["data"]["geometry"]["coordinates"];
        var long = coordinates[0];
        var lat = coordinates[1];
        this.geodata.push({
          "name": addresses[this.fetching_index]["name"],
          "addresse": addresses[this.fetching_index]["addresse"],
          "id": addresses[this.fetching_index]["id"],
          "longitude": long,
          "latitude": lat,
          "club_distance": this.calculate_distance(lat, long, CLUB_COORDS.latitude, CLUB_COORDS.longitude)
        });
      }
      for (var i = 0; i < this.geodata.length; i++) {
        this.inter_team_distance_map.push([]);
        for (var j = 0; j < this.geodata.length; j++) {
          var distance = this.calculate_distance(this.geodata[i].latitude, this.geodata[i].longitude, this.geodata[j].latitude, this.geodata[j].longitude);
          this.inter_team_distance_map[i].push(distance);
        }
      }
      this.fetching_index = this.registrant_data.length;
    },
    fetch_coords: function() {
      var _this = this;

      var map_diet = function(diet_string) {
        var str = diet_string.toLowerCase();
        var flags = 0;
        flags |= (str.includes('veget') ? DIET_FLAGS.VEGETARIAN : 0);
        flags |= (str.includes('vegan') ? DIET_FLAGS.VEGAN : 0);
        flags |= (str.includes('schwein') ? DIET_FLAGS.NO_PORK : 0);
        return flags;
      };

      this.mapped_registrant_data = this.registrant_data.map((e) => {
        return {
          "id": e[e.length - 1],
          "name": e[_this.teamname_column],
          "member_1": e[_this.member_1_column],
          "member_2": e[_this.member_2_column],
          "email": e[_this.email_column],
          "addresse": e[_this.addresse_column],
          "addresse_notes": e[_this.addresse_notes_column],
          "phone": e[_this.phone_column],
          "diet": map_diet(e[_this.diet_column]),
          "food_notes": e[_this.food_notes_column],
        };
      });

      _this.next_state();
      if (_this.mapped_registrant_data.length > 0) {
        _this.fetch_loop();
      } else {
        _this.show_error("No addresses available");
      }
    },
    next_state: function() {
      this.current_state += 1;
      switch (this.current_state) {
        case States.INITIAL: 
          this.$refs["initial-card"].scrollIntoView(true);
        break;
        case States.SELECT_COLUMN: 
          this.$refs["column-card"].scrollIntoView(true);
        break;
        case States.GEODATA: 
          this.$refs["geo-card"].scrollIntoView(true);
        break;
        case States.PAIRINGS: 
          this.$refs["pairing-card"].scrollIntoView(true);
        break;
        case States.EMAILS: 
          this.$refs["email-card"].scrollIntoView(true);
        break;
      }
    },
    select_teamname_column: function(e) {
      this.teamname_column = e.index;
    },
    select_member_1_column: function(e) {
      this.member_1_column = e.index;
    },
    select_member_2_column: function(e) {
      this.member_2_column = e.index;
    },
    select_email_column: function(e) {
      this.email_column = e.index;
    },
    select_addresse_column: function(e) {
      this.addresse_column = e.index;
    },
    select_addresse_notes_column: function(e) {
      this.addresse_notes_column = e.index;
    },
    select_phone_column: function(e) {
      this.phone_column = e.index;
    },
    select_diet_column: function(e) {
      this.diet_column = e.index;
    },
    select_food_notes_column: function(e) {
      this.food_notes_column = e.index;
    },
    parse_csv: function(str) {
        const records = parse(str);
        this.next_state();
        this.current_state = States.SELECT_COLUMN;
        this.columns = records[0];

        this.registrant_data = [];
        for (var i = 1; i < records.length; i++) {
          var _data = [];
          for (var j = 0; j < records[i].length; j++) {
            _data.push(records[i][j]);
          }
          _data.push(uuidv4());
          this.registrant_data.push(_data);
        }
    },
    show_message: function(message) {
      var _id = 1;
      if (this.notifications.length > 0) {
        _id = this.notifications[this.notifications.length - 1].id + 1;
      }
      this.notifications.push({
        type: 0,
        text: message,
        id: _id
      });
    },
    show_error: function(error) {
      var _id = 1;
      if (this.notifications.length > 0) {
        _id = this.notifications[this.notifications.length - 1].id + 1;
      }
      this.notifications.push({
        type: -1,
        text: error,
        id: _id
      });
    },
    handle_file: function(file) {
      var _this = this;
      var reader = new FileReader();
      reader.onload = function() {
        var text = reader.result;
        _this.parse_csv(text);
      }
      reader.readAsText(file);
    },
    remove_notification: function(id) {
      this.notifications = this.notifications.filter(function(e) {
        return e.id != id;
      });
    },
    toggle_column_help: function() {
      this.show_column_help = !this.show_column_help;
    }
  }
}
</script>


<style lang="scss">
  @font-face {
      font-family: 'Lato';
      src: url('./assets/Lato-Bold.woff2') format('woff2'),
          url('./assets/Lato-Bold.woff') format('woff');
      font-weight: bold;
      font-style: normal;
  }

  @font-face {
      font-family: 'Lato';
      src: url('./assets/Lato-Regular.woff2') format('woff2'),
          url('./assets/Lato-Regular.woff') format('woff');
      font-weight: normal;
      font-style: normal;
  }

  @font-face {
      font-family: 'Lato';
      src: url('./assets/Lato-Italic.woff2') format('woff2'),
          url('./assets/Lato-Italic.woff') format('woff');
      font-weight: normal;
      font-style: italic;
  }


  @import './assets/_variables.scss';
  @import './assets/main.scss';

  .notification-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    z-index: 2000;
    & .card.notification {
      margin-top: $height-xs;
      width: 100%;
      height: auto;
      color: $light;
      background-color: $success;
      &.error {
        background-color: $error;
      }
      & .text {
        flex-grow: 1;
        padding-right: $height-xs;
      }
      & .close {
        display: inline-block;
        height: $height-m;
        width: $height-m;
        background-position: center;
        background-size: $height-m;
        background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBkPSJNMTkgNi40MUwxNy41OSA1IDEyIDEwLjU5IDYuNDEgNSA1IDYuNDEgMTAuNTkgMTIgNSAxNy41OSA2LjQxIDE5IDEyIDEzLjQxIDE3LjU5IDE5IDE5IDE3LjU5IDEzLjQxIDEyeiIgZmlsbD0iI0ZGRkZGRiIvPjxwYXRoIGQ9Ik0wIDBoMjR2MjRIMHoiIGZpbGw9Im5vbmUiLz48L3N2Zz4=);
        &:hover {
          cursor: pointer;
        }
      }
    }
  }

  nav {
    h1 {
      font-size: 14px;
    }
    background-color: $light;
    box-shadow: 0 1px 1px 0 rgba($dark, 0.2);
  }

  main {
    & > * {
      width: 100%;
      margin-top: $height-m;
    }

    .card {
      h2 {
        font-size: 18px;
        margin: 0;
        margin-bottom: $height-s;
        &.with-help {
          position: relative;
          padding-right: $height-m;
          display: inline-block;
          width: auto;
          & .help {
            position: absolute;
            display: inline-block;
            height: $height-m;
            width: $height-m;
            background-position: center;
            background-size: $height-m;
            right: 0;
            top: 50%;
            transform: translateY(-$height-s);
            background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBmaWxsPSJub25lIiBkPSJNMCAwaDI0djI0SDB6Ii8+PHBhdGggZD0iTTExIDE4aDJ2LTJoLTJ2MnptMS0xNkM2LjQ4IDIgMiA2LjQ4IDIgMTJzNC40OCAxMCAxMCAxMCAxMC00LjQ4IDEwLTEwUzE3LjUyIDIgMTIgMnptMCAxOGMtNC40MSAwLTgtMy41OS04LThzMy41OS04IDgtOCA4IDMuNTkgOCA4LTMuNTkgOC04IDh6bTAtMTRjLTIuMjEgMC00IDEuNzktNCA0aDJjMC0xLjEuOS0yIDItMnMyIC45IDIgMmMwIDItMyAxLjc1LTMgNWgyYzAtMi4yNSAzLTIuNSAzLTUgMC0yLjIxLTEuNzktNC00LTR6Ii8+PC9zdmc+);
            &:hover {
              cursor: pointer;
            }
          }
        }
      }
      &.disabled {
        position: relative;
        &::after {
          content: '';
          position: absolute;
          display: block;
          left: 0;
          top: 0;
          width: 100%;
          height: 100%;
          z-index: 1000;
          background-color: rgba($dark, 0.1);
        }
      }
    }
  }

  .dropzone {
    width: 100%;
    height: 300px;
  }

  .column-selector {
    .label {
      padding-right: $height-xs;
      flex: 1;
    }
    .dropdown-container {
      width: 250px;
    }
    margin: $height-xs 0;
    &:not(:last-of-type) {
      border-top: 1px solid rgba($dark, 0.1);
    }
  }

  .geo-row {
    .geo-name {
      flex: 2;
    }
    .geo-addresse {
      flex: 2;
    }
    .geo-longitude {
      flex: 1;
    }
    
    .geo-latitude {
      flex: 1;
    }
    
    .geo-club-distance {
      flex: 1;
    }

    &:last-of-type {
      margin-bottom: $height-xs;
    }
  }

  .pairing-row {
    width: 100%;
    &.pairing-entry-row  {
      padding-bottom: $height-xs;
      padding-top: $height-xs;
    }
    &:not(:last-child) {
      border-bottom: 1px solid rgba($dark, 0.1);
    }
    .pairing-starter {
      flex: 1;
    }
    .pairing-main {
      flex: 1;
    }
    .pairing-dessert {
      flex: 1;
    }
  }

  .mail-content {
    textarea {
      width: 100%;
    }
  }

  .heart {
    display: inline-block;
    height: $height-m;
    width: $height-m;
    background-size: $height-m;
    background-position: center;
    background-repeat: no-repeat;
    background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBkPSJNMzUyIDU2aC0xYy0zOS43IDAtNzQuOCAyMS05NSA1Mi0yMC4yLTMxLTU1LjMtNTItOTUtNTJoLTFjLTYxLjkuNi0xMTIgNTAuOS0xMTIgMTEzIDAgMzcgMTYuMiA4OS41IDQ3LjggMTMyLjdDMTU2IDM4NCAyNTYgNDU2IDI1NiA0NTZzMTAwLTcyIDE2MC4yLTE1NC4zQzQ0Ny44IDI1OC41IDQ2NCAyMDYgNDY0IDE2OWMwLTYyLjEtNTAuMS0xMTIuNC0xMTItMTEzeiIvPjwvc3ZnPg==);
  }

  .coffee {
    display: inline-block;
    height: $height-m;
    width: $height-m;
    background-size: $height-m;
    background-position: center;
    background-repeat: no-repeat;
    background-image: url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBkPSJNMzY5LjcgNDA0SDExMC4xYy03LjYgMC0xNC4xIDUuOC0xNC40IDEzLjQtLjQgOCA2IDE0LjYgMTQgMTQuNmgyNTkuNmM3LjYgMCAxNC4xLTUuOCAxNC40LTEzLjQuMy04LTYuMS0xNC42LTE0LTE0LjZ6TTM5OS4yIDExOC41Yy4xLTEwLjgtNC4yLTIwLjktMTIuMi0yOC40LTcuMi02LjYtMTYuNy0xMC4xLTI2LjUtMTAuMUg4Ni44Yy05LjkgMC0xOS43IDMuNi0yNi45IDEwLjQtOC4xIDcuNy0xMi40IDE4LjItMTEuOSAyOS4zQzUwLjQgMTcyIDU4LjMgMjE4IDcxLjggMjU2LjNjMTEgMzEuNiAyNS44IDU4LjEgNDMuOSA3OC45IDMxLjggMzYuNiA2OC44IDQ4LjggNzcuOSA0OC44aDYwLjFjNS41IDAgMjUtNy4yIDQ0LjItMTkuNSAyMi40LTE0LjQgNDIuNC0zNi43IDU4LjEtNjQuNiAyIC4xIDQgLjIgNS45LjIgMjcuMiAwIDUyLjgtOS43IDcyLjEtMjcuNCAxOS40LTE3LjggMzAuMS00MS40IDMwLjEtNjYuNi0uMS0zOC45LTI1LjktNzMuNS02NC45LTg3LjZ6bS0yOS43IDE1My4xYzE0LjItMzQuNSAyMy42LTc2IDI3LjgtMTIzLjQgMCAwIDAtLjEuMSAwIDEwLjMgNSAxOS4xIDEyLjIgMjUuOCAyMC45IDguNCAxMC45IDEyLjggMjMuNyAxMi44IDM3IDAgMzQtMjkuMiA2Mi4xLTY2LjUgNjUuNXoiLz48L3N2Zz4=);
  }

  .footer-content {
    margin: $height-l 0;
  }

  .dontsoft-link {
    position: relative;
    display: inline-block;
    padding-right: $height-m + 4px;
    &::after {
        position: absolute;
        content: '';
        height: $height-m;
        width: $height-m;
        right: 0;
        top: 50%;
        transform: translateY(-$height-s);
        background-position: center;
        background-size: contain;
        background-repeat: no-repeat;
        background-image: url('https://dontsoft.com/logo.svg');
    }
  }
  .mail-overview-row {
    .name {
      flex: 1;
      padding-right: $height-xs;
    }
  }

  .organizer-column, .mail-content, .preview-column {
    margin-top: $height-xs;
  }

  .mailing-status {
    padding: $height-xs;
    margin: $height-xs 0;

    .mail {
      flex: 1;
      padding-right: $height-xs;
    }
    &.success {
      background-color: $success;
      color: $light;
      * {
        color: $light
      }
    }

    &.error {
      background-color: $error;
      color: $light;
      * {
        color: $light;
      }
    }
  }
</style>
