<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';;
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import BreezeInput from "@/components/ui/input/Input.vue";
import { usePage } from "@inertiajs/vue3";
import GthSuccess from "@/components/GthSuccess.vue";
import GthError from "@/components/GthError.vue";

const props = defineProps<{
    mathimata: Array;
    month: Number;
    year: Number;
    dateValues: Object;
    selectedMonth: String;
    exams: Object;
    formExams: Object;
    noExams: Object;
    initGridmode: Number;
   }>();

    const gridMode = ref(props.initGridmode);
    const oldDate = ref("");
    const examsIsOpen = reactive({ open: false });
    const showIsOpen = reactive({ open: false });
    const userExams = reactive({ data: [] });
    const examsEditMode = ref(false);
    const tmimata = reactive({ tmima1: [], tmima2: [] });
    const examsForm = useForm(props.formExams);
    const errMsg = reactive({ msg: "" });

    function previous() {
      let mm = props.month - 1;
      let yy = props.year;
      if (mm < 1) {
        mm = 12;
        yy = yy - 1;
      }
      router.get(
        route("exams", { y: yy, m: mm, g: gridMode.value }),
        {},
        {
          preserveScroll: true,
        }
      );
    }

    function next() {
      let mm = props.month + 1;
      let yy = props.year;
      if (mm > 12) {
        mm = 1;
        yy = yy + 1;
      }
      router.get(
        route("exams", { y: yy, m: mm, g: gridMode.value }),
        {},
        {
          preserveScroll: true,
        }
      );
    }

    function dateClicked(date) {
      if (
        !usePage().props.auth.user.permissions.admin &&
        props.noExams[date]
      )
        return;
      oldDate.value = date;
      axios.get("exams/tmimata/" + date).then(function (response) {
        tmimata.tmima1 = response.data;
      });
      examsEditMode.value = false;
      examsForm.reset();
      examsForm.date = date;
      errMsg.msg = "";
      examsIsOpen.open = true;
      const onEscape = (e) => {
        if (e.keyCode === 27) {
          examsIsOpen.open = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }

    function setTmima2Values(event) {
      tmimata.tmima2 = [];
      if (event.target.value) {
        Object.keys(tmimata.tmima1).forEach((key) => {
          if (
            tmimata.tmima1[key] !== event.target.value &&
            tmimata.tmima1[key] !== "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ"
          )
            tmimata.tmima2.push(tmimata.tmima1[key]);
        });
      } else {
        examsForm.tmima2 = "";
      }
    }

    function eventClicked(exam) {
      oldDate.value = exam.date;
      if (!usePage().props.auth.user.permissions.admin) {
        if (
          parseInt(exam.user_id) !==
          parseInt(usePage().props.auth.user.id)
        )
          return;
      }
      tmimata.tmima1 = [exam.tmima1];
      tmimata.tmima2 = [exam.tmima2];
      examsEditMode.value = true;
      examsForm.reset();
      examsForm.id = exam.id;
      examsForm.user_id = exam.user_id;
      examsForm.date = exam.date;
      examsForm.title = exam.title;
      examsForm.tmima1 = exam.tmima1;
      examsForm.tmima2 = exam.tmima2;
      examsForm.mathima = exam.mathima;
      errMsg.msg = "";
      examsIsOpen.open = true;
      const onEscape = (e) => {
        if (e.keyCode === 27) {
          examsIsOpen.open = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }

    function startDrag(evt, exam) {
      oldDate.value = exam.date;
      evt.dataTransfer.dropEffect = "move";
      evt.dataTransfer.effectAllowed = "move";
      evt.dataTransfer.setData("exam", JSON.stringify(exam));
    }

    function onDrop(evt, date) {
      if (date == oldDate) return;
      if(
        !usePage().props.auth.user.permissions.admin && 
        usePage().props.auth.user.id != JSON.parse(evt.dataTransfer.getData('exam')).user_id
        ) return;
      if (
        !usePage().props.auth.user.permissions.admin &&
        props.noExams[date]
      ) {
        errMsg.msg =
          "Όχι διαγωνίσματα τις " + new Date(date).toLocaleDateString();
        return;
      }
      const exam = JSON.parse(evt.dataTransfer.getData("exam"));
      router.put(
        route("exams.update", {
          event: exam.id,
          date: date,
        }),
        {},
        {
          preserveScroll: true,
        }
      );
    }

    function setBgColor(exam) {
      if (gridMode) {
        if (exam.tmima1 == "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ")
          return "text-white bg-red-500 hover:bg-red-700";
        if (exam.mathima == "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑ")
          return "text-white bg-red-500 hover:bg-red-700";
        if (
          exam.date.replace(/-/g, "") <
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id == usePage().props.auth.user.id
        )
          return "text-white bg-blue-500 hover:bg-blue-700 opacity-40";
        if (
          exam.date.replace(/-/g, "") <
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id !== usePage().props.auth.user.id
        )
          return "text-white bg-gray-500 hover:bg-gray-700 opacity-40";
        if (
          exam.date.replace(/-/g, "") >=
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id == usePage().props.auth.user.id
        )
          return "text-white bg-blue-500 hover:bg-blue-700";
        if (
          exam.date.replace(/-/g, "") >=
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id !== usePage().props.auth.user.id
        )
          return "text-white bg-gray-500 hover:bg-gray-700";
      } else {
        if (exam.tmima1 == "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ")
          return "text-white bg-red-500 hover:bg-red-700 md:ml-24 ";
          if (exam.mathima == "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑ")
          return "text-white bg-red-500 hover:bg-red-700 md:ml-24 ";
        if (
          exam.date.replace(/-/g, "") <
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id == usePage().props.auth.user.id
        )
          return "opacity-40 font-semibold text-blue-700 hover:text-blue-900 bg-transparent hover:bg-yellow-100 md:ml-24 border-0";
        if (
          exam.date.replace(/-/g, "") <
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id !== usePage().props.auth.user.id
        )
          return "opacity-40 font-semibold text-gray-700 hover:text-gray-900 bg-transparent hover:bg-yellow-100 md:ml-24 border-0";
        if (
          exam.date.replace(/-/g, "") >=
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id == usePage().props.auth.user.id
        )
          return "font-semibold text-blue-700 hover:text-blue-900 bg-transparent hover:bg-yellow-100  md:ml-24 border-0";
        if (
          exam.date.replace(/-/g, "") >=
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id !== usePage().props.auth.user.id
        )
          return "font-semibold text-gray-700 hover:text-gray-900 bg-transparent hover:bg-yellow-100 md:ml-24 border-0";
      }
    }
    function submitExamsForm() {
      if (!examsForm.date) {
        errMsg.msg = "Επιλέξτε ημερομηνία.";
        return;
      }
      if (!examsForm.tmima1) {
        errMsg.msg = "Επιλέξτε ένα τμήμα.";
        return;
      }
      if (
        !examsForm.mathima &&
        examsForm.tmima1 !== "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ"
      ) {
        errMsg.msg = "Επιλέξτε ένα μάθημα.";
        return;
      }
      let chkAllowExams = true;
      if (!usePage().props.auth.user.permissions.admin) {
        if (props.exams.hasOwnProperty(examsForm.date)) {
          props.exams[examsForm.date].forEach((exam) => {
            if (exam.tmima1 == "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ") chkAllowExams = false;
          });
        }
      }
      if (!chkAllowExams) {
        errMsg.msg =
          "Όχι διαγωνίσματα τις " +
          new Date(examsForm.date).toLocaleDateString();
        return;
      }
      if (!examsForm.id) {
        examsForm.post(route("exams.store"), {
          preserveScroll: true,
        });
        errMsg.msg = "";
        examsIsOpen.open = false;
        examsForm.reset();
      } else {
        if (examsForm.date == oldDate) {
          errMsg.msg = "";
          examsIsOpen.open = false;
          examsForm.reset();
          return;
        }
        router.put(
          route("exams.update", {
            event: examsForm.id,
            date: examsForm.date,
          }),
          {},
          {
            preserveScroll: true,
          }
        );
        examsIsOpen.open = false;
        examsForm.reset();
      }
    }

    function deleteExam() {
      router.delete(route("deleteExam", { id: examsForm.id }), {
        onBefore: () =>
          confirm(
            "Διαγραφή διαγωνίσματος\n\nΗμνια: " +
              new Date(examsForm.date).toLocaleDateString() +
              "\n" +
              examsForm.title +
              "\n\nΝα διαγραφεί;"
          ),
        preserveScroll: true,
      });
      examsIsOpen.open = false;
    }

    function myExams() {
      axios.get("userExams").then(function (response) {
        userExams.data = response.data;
        showIsOpen.open = true;
        const onEscape = (e) => {
          if (e.keyCode === 27) {
            showIsOpen.open = false;
            document.removeEventListener("keydown", onEscape);
          }
        };
        document.addEventListener("keydown", onEscape);
      });
    }

    function printTime() {
      return new Date().toLocaleTimeString();
    }

</script>

<template>
  <Head title="Διαγωνίσματα" />

  <AppLayout>

  <GthSuccess property="success">{{ printTime() }}</GthSuccess>
  <GthError property="error" />
  
    <div class="px-4 pt-4 w-full max-w-7xl mx-auto ">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div
            class="p-6 bg-white border-b border-gray-200 flex justify-between"
          >
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              Διαγωνίσματα
            </h2>
            <div class="w-max self-center space-x-1 sm:space-x-2">
          <a
            v-show="$page.props.auth.user.permissions.admin"
            class="gthButton"
            :href="
              route('exportExamsXls', {
                year: year,
                month: month,
              })
            "
          >
            Εξαγωγή
          </a>
          <button class="gthButton" @click="myExams">
            Τα διαγωνίσματά μου
          </button>
        </div>
          </div>
        </div>
      </div>

      <div>
      <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div id="calendar" class="p-4 bg-white border-b border-gray-200">
            <div
              class="
                font-bold
                text-xl
                pb-4
                flex
                space-x-2 space-y-2
                flex-col
                md:flex-row md:space-y-0
                items-center
                justify-center
              "
            >
              <span>{{ selectedMonth }}</span>
              <div class="flex space-x-2">
                <button
                  @click="previous()"
                  class="px-2 border rounded font-extrabold text-2xl"
                  title="προηγούμενος μήνας"
                >
                  &lt;
                </button>
                <Link
                  v-show="
                    month !==
                    parseInt(
                      new Date().toISOString().split('T')[0].substr(5, 2)
                    )
                  "
                  :href="
                    route(
                      'exams',
                      {
                        g: gridMode.value,
                      },
                      { preserveScroll: true }
                    )
                  "
                  class="px-1 border rounded font-extrabold text-2xl"
                  title="σήμερα"
                  preserve-state
                  preserve-scroll
                  >&#9962;</Link
                >
                <button
                  class="px-2 border rounded font-extrabold text-2xl"
                  @click="gridMode = !gridMode"
                  :title="gridMode ? 'Λίστα' : 'Πλέγμα'"
                >
                  {{ gridMode ? "&#9636;" : "&#9638;" }}
                </button>
                <button
                  @click="next()"
                  class="px-2 border rounded font-extrabold text-2xl"
                  title="επόμενος μήνας"
                >
                  &gt;
                </button>
              </div>
            </div>
            <div
              class="grid grid-cols-1 gap-0.5 bg-white :p-6 sm:pb-4"
              :class="{ 'md:grid-cols-5': gridMode }"
            >
              <div
                v-for="label in [
                  'Δευτέρα',
                  'Τρίτη',
                  'Τετάρτη',
                  'Πέμπτη',
                  'Παρασκευή',
                ]"
                :key="label"
                class="font-semibold text-gray-700 hidden border rounded px-2"
                :class="{ 'md:table-cell': gridMode }"
              >
                {{ label }}
              </div>
              <div
                v-for="date in dateValues"
                :key="date.date"
                class="
                  min-h-20
                  border border-gray-300
                  bg-white
                  hover:border-blue-500
                  rounded
                "
                :class="{
                  'md:min-h-12': !gridMode,
                  hidden: parseInt(date.date.substr(5, 2)) !== month,
                  'md:table-cell':
                    parseInt(date.date.substr(5, 2)) !== month && gridMode,
                  'bg-blue-100 hover:bg-blue-200':
                    date.date == new Date().toISOString().split('T')[0],
                  'bg-gray-50 hover:bg-gray-100':
                    parseInt(date.date.substr(5, 2)) !== month,
                  'hover:bg-yellow-50 ':
                    parseInt(date.date.substr(5, 2)) == month,
                  'bg-red-200 hover:bg-red-300': noExams[date.date],
                }"
                @click.self="dateClicked(date.date)"
                @drop="onDrop($event, date.date)"
                @dragover.prevent
                @dragenter.prevent
              >
                <div class="flex max-w-min">
                  <div
                    class="font-bold ml-1 pl-1 pr-2 w-10"
                    :class="{
                      'opacity-50': parseInt(date.date.substr(5, 2)) !== month,
                      'md:hidden': gridMode,
                    }"
                  >
                    {{ date.shortName }}
                  </div>
                  <div
                    class="font-bold"
                    :class="{
                      'opacity-50': parseInt(date.date.substr(5, 2)) !== month,
                    }"
                  >
                    {{ date.date.substr(-2) + "/" + date.date.substr(5, 2) }}
                  </div>
                </div>
                <div
                  v-for="exam in exams[date.date]"
                  :key="exam.id"
                  class="border border-gray-300 rounded mx-1 px-2 truncate"
                  :class="setBgColor(exam)"
                  :title="exam.title"
                  @click="eventClicked(exam)"
                  :draggable="
                    exam.user_id == $page.props.auth.user.id ||
                    $page.props.auth.user.permissions.admin
                  "
                  @dragstart="startDrag($event, exam)"
                >
                  {{ exam.title }}
                </div>
              </div>
            </div>
            <!-- ΕΠΙΚΕΦΑΛΙΔΕΣ ΠΑΛΙ -->
            <div
              class="
                font-bold
                text-xl
                flex
                space-x-2 space-y-2
                flex-col
                md:flex-row md:space-y-0
                items-center
                justify-center
              "
            >
              <span>{{ selectedMonth }}</span>
              <div class="flex space-x-2">
                <button
                  @click="previous()"
                  class="px-2 border rounded font-extrabold text-2xl"
                  title="προηγούμενος μήνας"
                >
                  &lt;
                </button>
                <Link
                  v-show="
                    month !==
                    parseInt(
                      new Date().toISOString().split('T')[0].substr(5, 2)
                    )
                  "
                  :href="
                    route(
                      'exams',
                      {
                        g: gridMode.value,
                      },
                      { preserveScroll: true }
                    )
                  "
                  class="px-1 border rounded font-extrabold text-2xl"
                  title="σήμερα"
                  preserve-state
                  preserve-scroll
                  >&#9962;</Link
                >
                <button
                  class="px-2 border rounded font-extrabold text-2xl"
                  @click="gridMode = !gridMode"
                  :title="gridMode ? 'Λίστα' : 'Πλέγμα'"
                >
                  {{ gridMode ? "&#9636;" : "&#9638;" }}
                </button>
                <button
                  @click="next()"
                  class="px-2 border rounded font-extrabold text-2xl"
                  title="επόμενος μήνας"
                >
                  &gt;
                </button>
              </div>
            </div>
            <!-- ΕΠΙΚΕΦΑΛΙΔΕΣ ΠΑΛΙ ΤΕΛΟΣ -->
          </div>
        </div>
      </div>
    </div>
  </AppLayout>

  <!-- MODAL EXAMS -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="examsIsOpen.open"
  >
    <div
      class="
        flex
        items-end
        justify-center
        min-h-screen
        pt-4
        px-4
        pb-20
        text-center
        sm:block sm:p-0
      "
    >
      <div class="fixed inset-0 transition-opacity">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- This element is to trick the browser into centering the modal contents. -->

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​

      <div
        class="
          inline-block
          align-bottom
          bg-white
          rounded-lg
          text-left
          overflow-hidden
          shadow-xl
          transform
          transition-all
          sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full
        "
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-headline"
      >
        <div
          class="font-semibold text-xl p-2 bg-gray-100 rounded-t-md mx-4 mt-4"
        >
          {{ examsEditMode ? "Επεξεργασία" : "Δήλωση" }}
          διαγωνίσματος
        </div>
        <div v-show="errMsg.msg" class="text-red-500 mx-4 p-2 text-center">
          {{ errMsg.msg }}
        </div>
        <div
          v-show="examsForm.title"
          class="text-gray-900 mx-4 p-2 text-center font-semibold text-xl"
        >
          {{ examsForm.title }}
        </div>
        <div
          class="
            grid grid-cols-3
            sm:grid-cols-6
            gap-4
            bg-white
            px-4
            pt-5
            pb-4
            sm:p-6 sm:pb-4
          "
        >
          <span class="sm:col-start-3 items-center">Ημνια</span>
          <BreezeInput
            type="date"
            v-model="examsForm.date"
            :disabled="!examsEditMode"
            @keydown.prevent
            @click="errMsg.msg = ''"
            class="w-full p-1 text-center col-span-2 disabled:opacity-50"
            :class="{
              border: examsEditMode,
              'select-none': !examsEditMode,
            }"
          />
          <span class="hidden sm:table-cell">&nbsp;</span>
          <span>Τμήμα</span>
          <select
            v-model="examsForm.tmima1"
            :value="examsForm.tmima1"
            :disabled="examsEditMode"
            @change="setTmima2Values($event)"
            class="
              border-gray-300
              focus:border-indigo-300
              focus:ring
              focus:ring-indigo-200
              focus:ring-opacity-100
              rounded-md
              shadow-sm
              sm:px-2
              w-full
              p-1
              col-span-2
              border
              disabled:opacity-50
            "
          >
            <option value=""></option>
            <option v-for="item in tmimata.tmima1" :key="item" :value="item">
              {{ item }}
            </option>
          </select>
          <span>Τμήμα</span>
          <select
            v-model="examsForm.tmima2"
            :value="examsForm.tmima2"
            :disabled="!examsForm.tmima1 || examsEditMode"
            class="
              border-gray-300
              focus:border-indigo-300
              focus:ring
              focus:ring-indigo-200
              focus:ring-opacity-100
              rounded-md
              shadow-sm
              sm:px-2
              w-full
              p-1
              col-span-2
              border
              disabled:opacity-50
            "
          >
            <option value=""></option>
            <option v-for="item in tmimata.tmima2" :key="item" :value="item">
              {{ item }}
            </option>
          </select>
          <span>Μάθημα</span>
          <select
            v-model="examsForm.mathima"
            :value="examsForm.mathima"
            :disabled="examsEditMode"
            class="
              border-gray-300
              focus:border-indigo-300
              focus:ring
              focus:ring-indigo-200
              focus:ring-opacity-100
              rounded-md
              shadow-sm
              sm:px-2
              w-full
              p-1
              col-span-2
              sm:col-span-5
              border
              disabled:opacity-50
            "
          >
            <option value=""></option>
            <option v-for="item in mathimata" :key="item" :value="item">
              {{ item }}
            </option>
          </select>
        </div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 text-right space-x-2">
          <button
            v-show="
              examsEditMode &&
              (examsForm.user_id == $page.props.auth.user.id ||
                $page.props.auth.user.permissions.admin)
            "
            @click="deleteExam"
            type="button"
            class="gthButton disabled:opacity-50"
          >
            Διαγραφή
          </button>
          <button
            v-show="
              !examsForm.user_id ||
              examsForm.user_id == $page.props.auth.user.id ||
              $page.props.auth.user.permissions.admin
            "
            @click="submitExamsForm"
            type="button"
            :disabled="!examsForm.isDirty"
            class="gthButton disabled:opacity-50"
            :class="{ 'cursor-not-allowed': !examsForm.isDirty }"
          >
            Αποθήκευση
          </button>
          <button
            @click="examsIsOpen.open = false"
            type="button"
            class="gthButton"
          >
            Άκυρο
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL EXAMS ΤΕΛΟΣ-->
  <!-- MODAL SHOW EXAMS -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="showIsOpen.open"
  >
    <div
      class="
        flex
        items-end
        justify-center
        min-h-screen
        pt-4
        px-4
        pb-20
        text-center
        sm:block sm:p-0
      "
    >
      <div class="fixed inset-0 transition-opacity">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- This element is to trick the browser into centering the modal contents. -->

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​

      <div
        class="
          inline-block
          align-bottom
          bg-white
          rounded-lg
          text-left
          overflow-hidden
          shadow-xl
          transform
          transition-all
          md:my-8 md:align-middle md:max-w-2xl md:w-full
        "
        :class="{
          'lg:max-w-4xl': $page.props.auth.user.permissions.admin,
        }"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-headline"
      >
        <div
          class="
            font-semibold
            text-xl
            bg-gray-100
            rounded-t-md
            mx-4
            mt-4
            p-4
          "
        >
          Τα διαγωνίσματά μου
        </div>
        <div class="text-gray-900 p-4" v>
          <div
            class="grid grid-cols-1 gap-1"
            :class="{
              'md:grid-cols-9': !$page.props.auth.user.permissions.admin,
              'md:grid-cols-12': $page.props.auth.user.permissions.admin,
            }"
          >
            <div class="md:text-center font-bold">Α/Α</div>
            <div class="md:text-center font-bold col-span-1 md:col-span-2">
              Ημ/νια
            </div>
            <div class="md:text-center font-bold col-span-1 md:col-span-2">
              Τμήμα
            </div>
            <div class="font-bold col-span-1 md:col-span-4">Μάθημα</div>
            <div
              v-show="$page.props.auth.user.permissions.admin"
              class="font-bold col-span-1 md:col-span-3"
            >
              Καθηγητής
            </div>

            <template v-for="(data, index) in userExams.data" :key="index">
              <div
                class="md:text-center"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.aa }}
              </div>
              <div
                class="md:text-center col-span-1 md:col-span-2"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.dateShow }}
              </div>
              <div
                class="md:text-center col-span-1 md:col-span-2 truncate"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.tmima }}
              </div>
              <div
                class="col-span-1 md:col-span-4 truncate"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.mathima }}
              </div>
              <div
                v-show="$page.props.auth.user.permissions.admin"
                class="col-span-1 md:col-span-3 truncate"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.teacher }}
              </div>
            </template>
          </div>
        </div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 text-right space-x-2">
          <button
            @click="showIsOpen.open = false"
            type="button"
            class="gthButton"
          >
            Εντάξει
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL EXAMS ΤΕΛΟΣ-->
</template>

<style scoped>
@reference "tailwindcss";
.gthButton {
  @apply bg-gray-100  hover:bg-gray-300  active:bg-gray-500  text-gray-700  hover:text-gray-900  active:text-gray-100
        text-sm  font-semibold  py-1 px-2 border border-gray-300  hover:border-transparent rounded-md;
}
</style>

