<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';;
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import SettingsPanel from "@/components/SettingsPanel.vue";
import { useForm } from "@inertiajs/vue3";
import GthSuccess from "@/components/GthSuccess.vue";
import GthError from "@/components/GthError.vue";
import BreezeInput from "@/components/ui/input/Input.vue";

const props = defineProps<{
    kathCount: String;
    mathCount: String;
    progCount: String;
    activeGradePeriod: String;
  }>();

    const kathForm = useForm({
      xls: null,
    });

    function kathSubmit() {
      kathForm.post(route("importKathigites"), { preserveScroll: true });
    }

    const mathForm = useForm({
      xls: null,
    });

    function mathSubmit() {
      mathForm.post(route("importStudents"), { preserveScroll: true });
    }

    const progForm = useForm({
      xls: null,
    });

    function progSubmit() {
      progForm.post(route("importProgram"), { preserveScroll: true });
    }

    const apouForm = useForm({
      xls: null,
    });

    function apouSubmit() {
      apouForm.post(route("importMyschoolApousies"), { preserveScroll: true });
    }

    const gradesForm = useForm({
      xls: null,
      rowLabels: 3,
      amColumn: 2,
      lessonColumn: 5,
    });

    function gradesSubmit() {
      gradesForm.post(route("insertGradesToDB"), { preserveScroll: true });
    }


</script>

<template>
  <Head title="Εισαγωγή xls" />


  <AppLayout>

    <div class="px-4 pt-4 w-full max-w-7xl mx-auto ">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div
            class="p-6 bg-white border-b border-gray-200 flex justify-between"
          >
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              Εισαγωγή&nbsp;xls
            </h2>
          </div>
        </div>
      </div>

    <GthSuccess property="success" />
    <GthError property="error" />

    <div>
      <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div
            class="
              p-4
              bg-white
              border-b border-gray-200
              space-y-4
            "
          >
            <!-- ΚΑΘΗΓΗΤΕΣ -->
            <SettingsPanel :title="'Καθηγητές ' + kathCount">
              <form @submit.prevent="kathSubmit">
                <div
                  class="
                    flex flex-col
                    space-y-1
                    sm:space-y-0 sm:flex-row sm:justify-between sm:space-x-1
                  "
                >
                  <input
                    type="file"
                    class="gthFile"
                    @input="kathForm.xls = $event.target.files[0]"
                  />
                  <button
                    type="submit"
                    class="gth"
                    :disabled="!kathForm.xls || kathForm.processing"
                  >
                    Εισαγωγή_xls
                  </button>
                  <a
                    href="exportKathXls"
                    as="button"
                    type="button"
                    class="gth"
                    >{{ kathCount < 2 ? "Πρότυπο_xls" : "Εξαγωγή_xls" }}</a
                  >
                  <Link
                    href="delKath"
                    method="delete"
                    as="button"
                    type="button"
                    class="gth"
                    :disabled="kathCount < 1"
                    onclick="return confirm('Θέλετε να διαγράψετε όλους τους καθηγητές?\nΕπιβεβαιώστε παρακαλώ.');"
                    >Διαγραφή</Link
                  >
                </div>
              </form>
            </SettingsPanel>
            <!-- ΚΑΘΗΓΗΤΕΣ ΤΕΛΟΣ-->

            <!-- ΜΑΘΗΤΕΣ -->
            <SettingsPanel :title="'Μαθητές ' + mathCount">
              <form @submit.prevent="mathSubmit">
                <div
                  class="
                    flex flex-col
                    space-y-1
                    sm:space-y-0 sm:flex-row sm:justify-between sm:space-x-1
                  "
                >
                  <input
                    type="file"
                    class="gthFile"
                    @input="mathForm.xls = $event.target.files[0]"
                  />
                  <button
                    type="submit"
                    class="gth"
                    :disabled="!mathForm.xls || mathForm.processing"
                  >
                    Εισαγωγή_xls
                  </button>
                  <a
                    href="exportStudXls"
                    as="button"
                    type="button"
                    class="gth"
                    >{{ mathCount < 1 ? "Πρότυπο_xls" : "Εξαγωγή_xls" }}</a
                  >
                  <Link
                    href="delStud"
                    method="delete"
                    as="button"
                    type="button"
                    class="gth"
                    :disabled="mathCount < 1"
                    onclick="return confirm('Θέλετε να διαγράψετε όλους τους μαθητές?\nΕπιβεβαιώστε παρακαλώ.');"
                    >Διαγραφή</Link
                  >
                </div>
              </form>
            </SettingsPanel>
            <!-- ΜΑΘΗΤΕΣ ΤΕΛΟΣ-->

            <!-- ΠΡΟΓΡΑΜΜΑ -->
            <SettingsPanel
              :title="'Πρόγραμμα ' + (progCount ? 'ώρες ' + progCount : '')"
            >
              <form @submit.prevent="progSubmit">
                <div
                  class="
                    flex flex-col
                    space-y-1
                    sm:space-y-0 sm:flex-row sm:justify-between sm:space-x-1
                  "
                >
                  <input
                    type="file"
                    class="gthFile"
                    @input="progForm.xls = $event.target.files[0]"
                  />
                  <button
                    type="submit"
                    class="gth"
                    :disabled="!progForm.xls || progForm.processing"
                  >
                    Εισαγωγή_xls
                  </button>
                  <a
                    href="exportProgXls"
                    as="button"
                    type="button"
                    class="gth"
                    >{{ progCount < 1 ? "Πρότυπο_xls" : "Εξαγωγή_xls" }}</a
                  >
                  <Link
                    href="delProg"
                    method="delete"
                    as="button"
                    type="button"
                    class="gth"
                    :disabled="progCount < 1"
                    onclick="return confirm('Θέλετε να διαγράψετε το πρόγραμμα?\nΕπιβεβαιώστε παρακαλώ.');"
                    >Διαγραφή</Link
                  >
                </div>
              </form>
            </SettingsPanel>
            <!-- ΠΡΟΓΡΑΜΜΑ ΤΕΛΟΣ-->
            <!-- ΑΠΟΥΣΙΕΣ -->
            <SettingsPanel title="Απουσίες από το myschool">
              <div
                class="
                  flex flex-col
                  space-y-1
                  sm:space-y-0 sm:flex-row sm:justify-between sm:space-x-1
                "
              >
                <input
                  type="file"
                  class="gthFile"
                  @input="apouForm.xls = $event.target.files[0]"
                />
                <button
                  type="button"
                  class="gth"
                  @click="apouSubmit"
                  :disabled="!apouForm.xls || apouForm.processing"
                >
                  Εισαγωγή_xls
                </button>
                <a type="button" class="gth" href="exportMyschoolApousies">
                  Πρότυπο_xls
                </a>
              </div>
              <div class="mt-4 text-xs">
                Κάνοντας εισαγωγή απουσιών από το Myschool δεν έχουμε δεδομένα
                για την ώρα (1η - 7η) που έγιναν οι απουσίες. Οι απουσίες που
                δεν είναι καταχωρισμενες ή διαφέρουν (περισσότερες ή λιγότερες)
                από τις εισηγμένες καταχωρίζονται ως εξής:
                <ul>
                  <li class="ml-6">
                    -> περισσότερες -> προστίθενται στις πρώτες ώρες χωρίς
                    απουσία
                  </li>
                  <li class="ml-6">
                    -> λιγότερες -> αφαιρούνται από τις τελευταίες ώρες με
                    απουσία
                  </li>
                </ul>
              </div>
            </SettingsPanel>
            <!-- ΑΠΟΥΣΙΕΣ ΤΕΛΟΣ-->
            <!-- ΒΑΘΜΟΙ -->
            <SettingsPanel title="Βαθμοί από 187.xls">
              <div v-show="!activeGradePeriod" class="text-center">
                Επιλέξτε βαθμολογική περίοδο στις
                <Link :href="route('settings')" class="underline"
                  >ρυθμίσεις</Link
                >
              </div>
              <div
                v-show="activeGradePeriod"
                class="
                  flex flex-col
                  space-y-1
                  sm:space-y-0 sm:flex-row sm:justify-between sm:space-x-1
                "
              >
                <input
                  type="file"
                  class="gthFile"
                  @input="gradesForm.xls = $event.target.files[0]"
                />
                <button
                  type="button"
                  class="gth"
                  @click="gradesSubmit"
                  :disabled="!gradesForm.xls || gradesForm.processing"
                >
                  Εισαγωγή_xls
                </button>
                <div class="text-center font-semibold">
                  {{ activeGradePeriod }}
                </div>
              </div>
              <div
                v-show="activeGradePeriod"
                class="
                  mt-4
                  flex flex-col
                  space-y-1
                  text-center
                  sm:space-y-0 sm:flex-row sm:justify-between sm:space-x-1
                "
              >
                <div>
                  Γραμμή επικεφαλίδων
                  <BreezeInput
                    name="rowLabels"
                    v-model="gradesForm.rowLabels"
                    class="py-1 w-10 border text-center"
                  />
                </div>
                <div>
                  Κολώνα Αρ. Μητρώου
                  <BreezeInput
                    name="amColumn"
                    v-model="gradesForm.amColumn"
                    class="py-1 w-10 border text-center"
                  />
                </div>
                <div>
                  Κολώνα 1ου μαθήματος
                  <BreezeInput
                    name="lessonColumn"
                    v-model="gradesForm.lessonColumn"
                    class="py-1 w-10 border text-center"
                  />
                </div>
              </div>
            </SettingsPanel>
            <!-- ΒΑΘΜΟΙ ΤΕΛΟΣ-->
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@reference "tailwindcss";
.gth {
  @apply w-full sm:w-max bg-gray-100  hover:bg-gray-300  active:bg-gray-500  text-gray-700  hover:text-gray-900  active:text-gray-100
        text-sm  font-semibold  py-1 px-4 border border-gray-300  hover:border-transparent rounded-md self-center disabled:opacity-50 text-center;
}
.gthFile {
  @apply w-full sm:w-2/5 md:w-1/2 p-0.5 text-gray-700  text-sm  font-semibold  border border-gray-300  hover:border-transparent rounded-md self-center;
}
</style>

