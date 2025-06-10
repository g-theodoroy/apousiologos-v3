<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';;
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { computed } from "vue";
import BreezeInput from "@/components/ui/input/Input.vue";
import { useForm } from "@inertiajs/vue3";
import GthSuccess from "@/components/GthSuccess.vue";

const props = defineProps<{
    anatheseis: Array;
    selectedAnathesiId: Number;
    selectedTmima: String;
    selectedMathima: String;
    arrStudents: Array;
    gradesStudentsPeriod?: Object;
    gradesPeriodLessons: Object;
    mathimata: Array;
    activeGradePeriod: String;
    periods: Object;
    showOtherGrades: Boolean;
    gradeBaseAlert: Number;
    infoInsertedAnatheseis: String;
    infoNotInsertedAnatheseis: Number;
   }>();

    const studentGrades = ref("");
    const isOpen = ref(false);
    const gradesForm = useForm(props.gradesStudentsPeriod);
    const showMathimaInLabels = computed(function () {
      let checkMathima = "";
      let math = {};
      props.anatheseis.forEach((anathesi) => {
        if (checkMathima !== anathesi.mathima) {
          math[anathesi.id] = true;
        } else {
          math[anathesi.id] = false;
        }
        checkMathima = anathesi.mathima;
      });
      return math;
    });
    function gradesSubmit() {
      // validation των βαθμών
      let notProperGrade = "";
      let notProperGradeMessage = "";
      props.arrStudents.forEach((student) => {
        let check = false;
        let grade = gradesForm[student.id][props.activeGradePeriod]
        if (grade && grade.trim()) {
          
          // Α & Β ΤΕΤΡ όχι αριθμός εκτός από 'Δ'
          if (
            props.activeGradePeriod < 3 &&
            isNaN(grade) &&
            grade !== "Δ"
          ) {
            //alert('Α & Β ΤΕΤΡ όχι αριθμός εκτός από Δ')
            check = true;
          }
          // ΕΞ-ΙΟΥΝ όχι αριθμός
          if (
            props.activeGradePeriod == 3 &&
            isNaN(grade.replace(',','.'))
          ) {
            //alert("ΕΞ-ΙΟΥΝ όχι αριθμός");
            check = true;
          }
          // Α & Β ΤΕΤΡ αριθμός όχι θετικός ακέραιος
         if (
            props.activeGradePeriod < 3 &&
            !isNaN(grade.replace(',','.')) &&
            !/^\d{1,2}$/.test(grade)
          ) {
            //alert("Α & Β ΤΕΤΡ αριθμός όχι θετικός ακέραιος");
            check = true;
          }
          // ΕΞ-ΙΟΥΝ θετικός ακέραιος ή με ένα δεκαδικό ή -1
         if (
            props.activeGradePeriod == 3 &&
            !isNaN(grade.replace(',','.')) &&
            !/^\d{1,2}(?:[\.\,]\d)?$/.test(grade)&&
            parseFloat(grade.replace(',','.')) !== -1
          ) {
            //alert("ΕΞ-ΙΟΥΝ αριθμός όχι σωστος");
            check = true;
          }
          //  όχι > 20
         if (
           !isNaN(grade.replace(',','.')) && 
           grade.replace(',','.') > 20
          ) {
            //alert(">20");
            check = true;
          }
          if (check) {
            notProperGrade +=
              "\n     " +
              student.eponimo +
              " " +
              student.onoma +
              ": " +
              grade;
          }
        }
      });

      if (notProperGrade) {
        alert(
          "Μπορείτε να καταχωρίσετε" +
            "\n" +
            "     μόνο αριθμούς από το 0 έως το 20\n" +
            "               ακέραιους στο Α & Β Τετράμηνο\n" +
            "               ακέραιους ή με 1 δεκαδικό ψηφίο στα Γραπτά\n" +
            "      ''Δ'' αν δεν έχετε άποψη για βαθμό Τετραμήνου\n" +
            "     ''-1'' αν δεν προσήλθε σε Γραπτή εξέταση\n" +
            "\n" +
            "Παρακαλώ ελέγξτε τα ακόλουθα:" +
            notProperGrade
        );
        return false;
      }

      if (props.gradeBaseAlert == 0) {
        gradesForm.post(route("grades.store") + "/" + props.selectedAnathesiId);
      }
      let underBase = "";
      props.arrStudents.forEach((student) => {
        let grade = gradesForm[student.id][props.activeGradePeriod]
        if (
          grade &&
          grade.trim() &&
          grade < props.gradeBaseAlert &&
          parseFloat(grade.replace(',','.')) !== -1
        ) {
          underBase +=
            "\n" +
            student.eponimo +
            " " +
            student.onoma +
            ": " +
            grade;
        }
      });
      let answer = true;
      if (underBase) {
        answer = confirm(
          "Προσοχή!\nΚαταχωρίζονται βαθμοί κάτω από τη βάση του " +
            props.gradeBaseAlert +
            ".\n" +
            underBase +
            "\n\nΘέλετε να συνεχιστεί η καταχώριση?"
        );
      }
      if (answer) {
        gradesForm.post(route("grades.store") + "/" + props.selectedAnathesiId, { preserveScroll: true });
      }
    }
    const showGrades = computed(function () {
      let showGr = {};
      // δίνει true μόνο αν καταχωριστεί και άλλο μάθημα στο ίδιο τρίμηνο
      props.arrStudents.forEach((student) => {
        if (!props.gradesPeriodLessons[student.id]) {
          showGr[student.id] = false;
        } else {
          showGr[student.id] = false;
          props.mathimata.forEach((mathima) => {
            if (mathima !== props.selectedMathima) {
              if (props.gradesPeriodLessons[student.id][mathima]) {
                if (
                  props.gradesPeriodLessons[student.id][mathima][
                    props.activeGradePeriod
                  ]
                ) {
                  showGr[student.id] = true;
                }
              }
            }
          });
        }

        /*
        // ενεργοποιείται αν υπάρχει βαθμός σε άλλο μάθημα σε οποιοδήποτε τρίμηνο
        if (!props.gradesPeriodLessons[student.id]) {
          showGr[student.id] = false;
        } else {
          if (
            (!props.gradesPeriodLessons[student.id][props.selectedMathima] &&
              Object.keys(props.gradesPeriodLessons[student.id]).length > 1) ||
            (props.gradesPeriodLessons[student.id][props.selectedMathima] &&
              Object.keys(props.gradesPeriodLessons[student.id]).length > 2)
          ) {
            showGr[student.id] = true;
          } else {
            showGr[student.id] = false;
          }
        }
        */
      });
      return showGr;
    });
    function showAllGradesFor(am) {

      //if (!showGrades[am]) return;
     
      // φτιάχνω ένα table με τους βαθμούς
      // Επικεφαλίδα ΟΝΟΜΑΤΕΠΩΝΥΜΟ
      studentGrades.value =
        "<div class='font-semibold text-xl p-2 bg-gray-100 rounded-t-md'>" +
        props.gradesPeriodLessons[am]["name"] +
        "</div>";
      // κεφαλίδες table ΜΑΘΗΜΑ
      studentGrades.value += `
        <table class="w-full mt-4">
        <tr><th>ΜΑΘΗΜΑ</th>`;
      // κεφαλίδες taable βαθμολογικές περίοδοι
      for (var index in props.periods) {
        if (index <= props.activeGradePeriod) {
          studentGrades.value +=
            "<th class='text-center'>" +
            props.periods[index].substr(0, 6) +
            "</th>";
        }
      }
      studentGrades.value += "</tr>";
      // μαθήματα
      for (var mathima in props.gradesPeriodLessons[am]) {
        if (mathima == "name") continue;
        studentGrades.value += "<tr><td>" + mathima + "</td>";
        // βαθμοί για κάθε περίοδο
        for ( var i = 1 ; i <= props.activeGradePeriod ; i++) {
          if (props.gradesPeriodLessons[am][mathima][i]) {
            studentGrades.value +=
              "<td  class='text-center'>" +
              props.gradesPeriodLessons[am][mathima][i] +
              "</td>";
          }else{
            studentGrades.value +=  "<td  class='text-center'>&nbsp;</td>";
          }
        }
        studentGrades.value += "</tr>";
      }
      studentGrades.value += "</table>";

      // άνοιγμα του MODAL
      isOpen.value = true;
      const onEscape = (e) => {
        if (isOpen && e.keyCode === 27) {
          isOpen.value = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }
    function printTime() {
      return new Date().toLocaleTimeString();
    }

</script>

<template>
  <Head title="Βαθμολογία" />

  <AppLayout>

    <div class="px-4 pt-4 w-full max-w-7xl mx-auto ">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div
            class="p-6 bg-white border-b border-gray-200 flex justify-between"
          >
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              Βαθμολογία
            </h2>
            <div v-show="activeGradePeriod != 0" class="w-max self-center space-x-1 sm:space-x-2">
          <a
            v-show="infoInsertedAnatheseis"
            class="gth"
            :href="route('insertedGrades',[1])"
          >
            Καταχωρίστηκαν {{infoInsertedAnatheseis}}
          </a>
          <a
            v-show="infoNotInsertedAnatheseis"
            class="gth"
            :href="route('insertedGrades',[0])"
          >
            Υπολείπονται {{infoNotInsertedAnatheseis}}
          </a>
          <a
            v-show="!infoNotInsertedAnatheseis"
            class="gth"
            :href="route('insertedGradesStudents')"
          >
            Μαθητές χωρίς βαθμούς
          </a>
        </div>
          </div>
        </div>
      </div>

    <div>
      <div class="max-w-7xl mx-auto px-4">
        <!-- CONTAINER ΣΕΛΙΔΑΣ -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-4 bg-white border-b border-gray-200 space-y-4">
            <!-- ΣΥΝΔΕΣΜΟΙ ΤΜΗΜΑΤΩΝ -->
            <div class="flex flex-wrap border border-gray-200 rounded-md p-4">
              <span class="font-bold">Επιλογή μαθήματος - τμήματος:</span>
              <div
                v-for="anathesi in anatheseis"
                :key="anathesi.id"
                class="space-x-1"
              >
                <span
                  v-show="showMathimaInLabels[anathesi.id]"
                  class="font-semibold"
                  :class="{ 'pl-1': showMathimaInLabels[anathesi.id] }"
                  >{{ anathesi.mathima }}:</span
                >
                <Link
                  :href="route('grades', { a: anathesi.id })" 
                  class="mr-1"
                  :class="{
                    'bg-gray-500 text-gray-100 font-bold px-2 rounded-md':
                      selectedAnathesiId === anathesi.id,
                  }"
                  :title="
                    'Επιλογή ' + anathesi.mathima + ' - ' + anathesi.tmima
                  "
                  >{{ anathesi.tmima }}</Link
                >
              </div>
              <Link
                v-show="$page.props.auth.user.permissions.admin"
                :href="route('grades')"
                class="text-xl pl-2"
                :class="{
                  'bg-gray-500 text-gray-100 px-2 rounded-md font-bold':
                    !selectedAnathesiId,
                }"
                title="Αποεπιλογή μαθήματος - τμήματος"
                >&#9746;</Link
              >
            </div>
            <!-- ΣΥΝΔΕΣΜΟΙ ΤΜΗΜΑΤΩΝ ΤΕΛΟΣ-->
            <div class="font-bold text-center text-2xl text-gray-900">
              {{ periods[activeGradePeriod] }}
            </div>
            <GthSuccess>{{ printTime() }}</GthSuccess>
            <div
              v-show="!selectedAnathesiId"
              class="text-center font-semibold text-3xl text-gray-700 py-24"
            >
              Επιλέξτε ένα τμήμα.
            </div>
            <!-- ΚΑΡΤΕΛΑ ΤΜΗΜΑΤΟΣ -->
            <div
              v-show="selectedAnathesiId"
              class="flex flex-col rounded-md max-w-3xl mx-auto"
            >
              <!-- ΤΜΗΜΑ & ΚΟΥΜΠΙΑ -->
              <div
                class="
                  pt-4
                  flex flex-col
                  space-y-2
                  text-center
                  sm:px-20 sm:flex-row sm:space-y-0 sm: sm:justify-between
                "
              >
                <div class="font-semibold">
                  {{ selectedTmima }} -> {{ selectedMathima }}
                </div>
                <button
                  v-show="activeGradePeriod !== '0'"
                  @click="gradesSubmit"
                  class="gth"
                >
                  Αποθήκευση
                </button>
              </div>
              <!-- ΤΜΗΜΑ & ΚΟΥΜΠΙΑ ΤΕΛΟΣ-->
              <!-- ΚΕΦΑΛΙΔΕΣ ΠΙΝΑΚΑ -->
              <div
                class="
                  pt-4
                  pb-1
                  flex flex-col
                  space-y-1
                  text-center
                  sm:flex-row sm:space-y-0 sm: sm:justify-between
                "
              >
                <div class="font-semibold">Ονοματεπώνυμο</div>
                <div
                  v-show="activeGradePeriod !== '0'"
                  class="
                    flex
                    w-max
                    justify-evenly
                    border-2 border-gray-300
                    p-1
                    rounded-md
                    self-center
                    bg-gray-100
                    font-semibold
                  "
                >
                  <div v-show="showOtherGrades" class="w-6">
                    <span class="px-1"> &#128065; </span>
                  </div>
                  <div
                    v-show="index <= activeGradePeriod"
                    v-for="(period, index) in periods"
                    :key="index"
                    class="w-16"
                  >
                    {{ period.substr(0, 6) }}
                  </div>
                  <div class="w-24 text-left mr-1">Ολογράφως</div>
                </div>
              </div>
              <!-- ΚΕΦΑΛΙΔΕΣ ΠΙΝΑΚΑ ΤΕΛΟΣ -->
              <!-- ΓΡΑΜΜΕΣ ΠΙΝΑΚΑ -->
              <div
                v-for="(student, index) in arrStudents"
                :Key="student.id"
                class="py-0.5 flex flex-col sm:flex-row sm:justify-between"
              >
                <div class="flex flex-row space-x-2 pt-2">
                  <div class="w-6 text-right">{{ index + 1 }}.</div>
                  <div class="w-10">
                    {{ student.id }}
                  </div>
                  <div>
                    {{ student.eponimo }}
                    {{ student.onoma }}
                  </div>
                </div>
                <div
                  v-show="activeGradePeriod !== '0'"
                  class="
                    flex
                    w-max
                    justify-evenly
                    border-2 border-gray-300
                    px-1
                    py-0.5
                    rounded-md
                    self-center
                    bg-gray-100
                  "
                >
                  <div
                    v-show="showOtherGrades"
                    class="w-6 self-center"
                    @click="showAllGradesFor(student.id)"
                    :title="
                      'δείξε όλους τους βαθμούς για ' +
                      student.eponimo +
                      ' ' +
                      student.onoma
                    "
                  >
                    <span
                      v-show="showGrades[student.id]"
                      class="bg-blue-200 px-1 rounded-md border cursor-pointer"
                    >
                      &#128065;
                    </span>
                  </div>
                  <div
                    v-show="index <= activeGradePeriod"
                    v-for="(period, index) in periods"
                    :key="index"
                    class="w-16 text-center"
                  >
                    <BreezeInput
                      v-model="gradesForm[student.id][index]"
                      class="w-12 h-7 text-center enabled:bg-white"
                      :disabled="index !== activeGradePeriod"
                    />
                  </div>
                  <div class="w-24 mr-1 self-center">
                    {{ student.olografos }}
                  </div>
                </div>
              </div>
              <!-- ΓΡΑΜΜΕΣ ΠΙΝΑΚΑ ΤΕΛΟΣ -->
              <!-- ΤΜΗΜΑ & ΚΟΥΜΠΙΑ ΕΠΑΝΑΛΗΨΗ-->
              <div
                class="
                  pt-4
                  flex flex-col
                  space-y-2
                  text-center
                  sm:px-20 sm:flex-row sm:space-y-0 sm: sm:justify-between
                "
              >
                <div class="font-semibold">
                  {{ selectedTmima }} -> {{ selectedMathima }}
                </div>
                <button
                  v-show="activeGradePeriod !== '0'"
                  @click="gradesSubmit"
                  class="gth"
                >
                  Αποθήκευση
                </button>
              </div>
              <!-- ΤΜΗΜΑ & ΚΟΥΜΠΙΑ ΕΠΑΝΑΛΗΨΗ ΤΕΛΟΣ-->
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
  <!-- MODAL -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="isOpen"
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
          sm:my-8 sm:align-middle sm:max-w-lg sm:w-full
          md:max-w-xl
        "
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-headline"
      >
        <div
          class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4"
          v-html="studentGrades"
        ></div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button @click="isOpen = false" type="button" class="gth">
            Εντάξει
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL ΤΕΛΟΣ-->
</template>
<style scoped>
@reference "tailwindcss";
.gth {
  @apply w-full sm:w-max bg-gray-100  hover:bg-gray-300  active:bg-gray-500  text-gray-700  hover:text-gray-900  active:text-gray-100
        text-sm  font-semibold  py-1 px-4 border border-gray-300  hover:border-transparent rounded-md self-center disabled:opacity-50;
}
</style>
