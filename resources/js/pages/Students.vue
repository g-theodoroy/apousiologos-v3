<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import Pagination from "@/components/Pagination.vue";
import BreezeInput from "@/components/ui/input/Input.vue";
import { watch, reactive, ref, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import GthSuccess from "@/components/GthSuccess.vue";
import BreezeCheckbox from "@/components/ui/checkbox/Checkbox.vue";

const props = defineProps<{
    students: Object;
    arrNames: Object;
    tableLabels: Array;
    filters: Object;
    fields: Array;
    queryStr: String;
    iniShowApouForStu: Object;
    tableApouLabels: Array;
    apousiesForStudent: Object;
    tmimataRows: Number;
    totalHours: Number;
    formStudents: Object;
    formApousies: Object;
   }>();


    const showApouForStu = ref(props.iniShowApouForStu);
    const stuIsOpen = reactive({ open: false });
    const apouIsOpen = ref(false);
    const stuEditMode = ref(false);
    const apouEditMode = ref(false);
    const stuForApousies = ref("");
    const stuForm = useForm(props.formStudents);
    const apouForm = useForm(props.formApousies);
    const errMsg = reactive({ msg: "" });
    const params = reactive({
      page: props.filters.page,
      rows: props.filters.rows,
      search: props.filters.search,
      field: props.filters.field,
      direction: props.filters.direction,
    });
    watch(params, (currentValue, oldValue) => {
      let prms = params;
      Object.keys(prms).forEach((key) => {
        if (prms[key] == "") {
          delete prms[key];
        }
      });
      router.get(route("students"), prms, {
        replace: true,
        preserveState: true,
        preserveScroll: true,
      });
    });

    function sort(field) {
      if (field == "") return;
      if (params.field !== field) {
        params.direction = "asc";
      } else {
        params.direction = params.direction == "asc" ? "desc" : "asc";
      }
      params.field = field;
    }

    function editStudent(student) {
      stuForm.reset();
      errMsg.msg = "";
      if (student) {
        stuEditMode.value = true;
        stuForm.id = student.id;
        stuForm.oldId = student.id;
        stuForm.eponimo = student.eponimo;
        stuForm.onoma = student.onoma;
        stuForm.patronimo = student.patronimo;
        stuForm.email = student.email;
        if (student.tmimataStr) {
          student.tmimataStr.split(", ").forEach((tmima, ind) => {
            stuForm.tmima[ind] = tmima;
          });
        }
      } else {
        stuEditMode.value = false;
      }
      stuIsOpen.open = true;
      const onEscape = (e) => {
        if (e.keyCode === 27) {
          stuIsOpen.open = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }

    function deleteStudent(student) {
      router.delete(`studentDelete/${student.id}`, {
        onBefore: () =>
          confirm(
            student.eponimo + " " + student.onoma + "\n\nΘέλετε να διαγραφεί?"
          ),
        preserveScroll: true,
      });
    }

    function editApousies(student, apousies) {
      apouForm.reset();
      errMsg.msg = "";
      stuForApousies.value = student.eponimo + " " + student.onoma;
      apouForm.student_id = student.id;
      if (apousies) {
        apouEditMode.value = true;
        apouForm.date = apousies.date;
        for (var key in apousies.arrApou.apou) {
          apouForm['apou'][key] = apousies.arrApou.apou[key];
          apouForm['apov'][key] = apousies.arrApou.apov[key];
          apouForm['teach'][key] = apousies.arrApou.teach[key];
        }
      } else {
        apouEditMode.value = false;
        apouForm.date = new Date().toISOString().split("T")[0];
      }
      apouIsOpen.value = true;
      const onEscape = (e) => {
        if (e.keyCode === 27) {
          apouIsOpen.value = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }

    function deleteApousies(student, apousies) {
      router.delete(`apousiesDelete/${apousies.id}`, {
        onBefore: () =>
          confirm(
            student.eponimo +
              " " +
              student.onoma +
              "\nΗμνια: " +
              apousies.dateShow +
              "\nΑπουσίες: " +
              apousies.sum +
              "\n\nΘέλετε να διαγραφoύν οι απουσίες?"
          ),
        preserveScroll: true,
      });
      if (props.apousiesForStudent[student.id].length < 2) {
        showApouForStu[student.id] = false;
      }
    }

    function submitStuForm() {
      if (!stuForm.id) {
        errMsg.msg = "Συμπληρώστε τον Αρ.Μητρώου";
        return;
      }
      if (isNaN(stuForm.id)) {
        errMsg.msg = `Ο Αρ. Μητρώου "${stuForm.id}" δεν είναι αριθμός`;
        return;
      }
      if (!stuForm.eponimo) {
        errMsg.msg = "Συμπληρώστε το Επώνυμο";
        return;
      }
      if (!stuForm.onoma) {
        errMsg.msg = "Συμπληρώστε το Όνομα";
        return;
      }
      let validRegex =
        /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
      if (stuForm.email && !stuForm.email.match(validRegex)) {
        errMsg.msg = `Το email "${stuForm.email}" δεν είναι έγκυρο`;
        return;
      }

      if (stuForm.id == stuForm.oldId) {
        stuForm.post(route("students.store"), { preserveScroll: true });
        stuForm.reset();
        stuIsOpen.open = false;
      } else {
        let id = stuForm.id;
        axios.get("studentUnique/" + id).then(function (response) {
          if (response.data > 0) {
            errMsg.msg = `Ο Αρ. Μητρώου "${id}" χρησιμοποιείται`;
            return;
          } else {
            stuForm.post(route("students.store"), { preserveScroll: true });
            stuForm.reset();
            stuIsOpen.open = false;
          }
        });
      }
    }

    function submitApouForm() {
      if (!apouForm.date) {
        errMsg.msg = "Επιλέξτε την ημερομηνία";
        return;
      }
      let apouExist = false;
      for (var key in apouForm['apou']){
        if (!isNaN(key)) {
          if (apouForm['apou'][key]) apouExist = true;
        }
      }
      if (!apouExist) {
        errMsg.msg = "Καταχωρίστε τούλαχιστον μία απουσία";
        return;
      }

      if (!apouEditMode) {
        let sameDate = null;
        props.apousiesForStudent[apouForm.student_id].forEach(
          (apousies) => {
            if (apousies.date == apouForm.date) {
              sameDate = apousies.dateShow;
              return;
            }
          }
        );
        if (sameDate) {
          if (
            !confirm(
              "Υπάρχουν καταχωρισμένες απουσίες τις " +
                sameDate +
                ".\nΑν συνεχίσετε θα αντικατασταθούν από τις παρούσες.\nΘέλετε ωστόσο να συνεχίσετε;"
            )
          )
            return;
        }
      }
      apouForm.post(route("apousiesStore"), { preserveScroll: true });
      apouForm.reset();
      apouIsOpen.value = false;
    }

    function printTime() {
      return new Date().toLocaleTimeString();
    }

        function toggleApovoli(index){
      if(apouForm['apov'][index]==false) {
        apouForm['apou'][index]=true
        apouForm['apov'][index]=true
        apouForm['teach'][index]=usePage().props.auth.user.id
      }else{
        apouForm['apov'][index]=false
      }
    }

    function toggleApousia(index){
      if(apouForm['apou'][index]==true) {
        apouForm['apov'][index]=false
        apouForm['teach'][index]=''
      }else{
        apouForm['teach'][index]=usePage().props.auth.user.id
      }
    }

</script>

<template>
  <Head title="Μαθητές" />

  <AppLayout>
    
    <GthSuccess>{{ printTime() }}</GthSuccess>

    <div class="px-4 pt-4 w-full max-w-7xl mx-auto ">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div
            class="p-6 bg-white border-b border-gray-200 flex justify-between"
          >
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              Μαθητές
            </h2>
            <button @click="editStudent()" class="gthButton mr-10 md:mr-0">
              Εισαγωγή μαθητή
            </button>
          </div>
        </div>
      </div>

      <div>
      <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

          <!-- ΓΡΑΜΜΕΣ & ΑΝΑΖΗΤΗΣΗ -->
          <div class="p-4 bg-white border-b border-gray-200">
            <div class="flex justify-between space-x-2">
              <select
                v-model="params.rows"
                :value="filters.rows"
                class="
                  border-gray-300
                  focus:border-indigo-300
                  focus:ring
                  focus:ring-indigo-200
                  focus:ring-opacity-100
                  rounded-md
                  shadow-sm
                  p-1
                  sm:px-2
                  w-20
                "
              >
                <option
                  v-for="item in [10, 20, 50, 100]"
                  :key="item"
                  :value="item"
                >
                  {{ item }}
                </option>
              </select>
              <BreezeInput
                v-model="params.search"
                type="text"
                class="w-3/4 sm:w-auto"
                placeholder="Αναζήτηση"
              />
            </div>
            <!-- ΓΡΑΜΜΕΣ & ΑΝΑΖΗΤΗΣΗ  ΤΕΛΟΣ-->

            <table
              class="
                w-full flex-row flex-no-wrap
                bg-white
                rounded-lg
                overflow-hidden
                sm:shadow-lg
                my-2
                inline-table
              "
            >
              <tbody class="flex-1 sm:flex-none space-y-2">
                <tr
                  class="
                    text-white
                    bg-gray-400
                    flex flex-col flex-no
                    wrap
                    sm:table-row
                    rounded-t-lg
                  "
                >
                  <td
                    v-for="(label, index) in tableLabels"
                    :key="label"
                    class="p-2 font-semibold"
                    :class="{
                      'text-left sm:text-center': index < 3,
                      'sm:hidden xl:table-cell':
                        index == tableLabels.length - 2,
                      'sm:hidden lg:table-cell':
                        index == tableLabels.length - 3,
                      'sm:hidden md:table-cell':
                        index == tableLabels.length - 4,
                    }"
                    :title="
                      index > 0 && index < 9
                        ? params.field !== fields[index] ||
                          (params.field == fields[index] &&
                            params.direction == 'desc')
                          ? 'αύξουσα ' + 'ταξινόμηση κατά ' + label
                          : 'φθίνουσα ' + 'ταξινόμηση κατά ' + label
                        : ''
                    "
                    @click="sort(fields[index])"
                  >
                    <span
                      v-show="
                        params.field == fields[index] &&
                        params.direction == 'asc'
                      "
                      >&#8599;</span
                    >
                    <span
                      v-show="
                        params.field == fields[index] &&
                        params.direction == 'desc'
                      "
                      >&#8600;</span
                    >
                    {{ label }}
                  </td>
                </tr>
                <template
                  v-for="(student, index) in students.data"
                  :key="student.id"
                >
                  <tr
                    class="
                      flex flex-col flex-no
                      wrap
                      sm:table-row
                      hover:bg-gray-100
                      text-sm
                    "
                  >
                    <td
                      class="
                        border-grey-light border
                        hover:bg-gray-100
                        p-2
                        text-left
                        sm:text-center
                      "
                    >
                      {{ index + students.from }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        text-left
                        sm:text-center
                      "
                    >
                      {{ student.id }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        text-left
                        sm:text-center
                      "
                    >
                      {{ student.sumap == 0 ? "&nbsp;" : student.sumap }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        text-left
                        sm:text-center
                      "
                    >
                      {{ student.sumapov == 0 ? "&nbsp;" : student.sumapov }}
                    </td>
                    <td class="border-grey-light border p-2">
                      {{ student.eponimo }}
                    </td>
                    <td class="border-grey-light border p-2">
                      {{ student.onoma }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        sm:hidden
                        md:table-cell
                      "
                    >
                      {{ student.patronimo }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        sm:hidden
                        lg:table-cell
                      "
                    >
                      {{ student.email }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        sm:hidden
                        xl:table-cell
                      "
                    >
                      {{ student.tmimataStr }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        truncate
                        text-left
                        sm:text-center
                        space-x-2
                      "
                    >
                      <button
                        @click="
                          student.sumap
                            ? (showApouForStu[student.id] =
                                !showApouForStu[student.id])
                            : false
                        "
                        class="
                          px-0.5
                          border
                          rounded
                          bg-green-200
                          disabled:opacity-30
                          disabled:bg-gray-100
                          disabled:cursor-not-allowed
                        "
                        :class="{
                          'cursor-not-allowed': !student.sumap,
                        }"
                        :disabled="!student.sumap"
                        :title="
                          student.sumap
                            ? showApouForStu[student.id]
                              ? 'Κρύψε απουσίες για ' +
                                student.eponimo +
                                ' ' +
                                student.onoma
                              : 'Δείξε απουσίες για ' +
                                student.eponimo +
                                ' ' +
                                student.onoma
                            : ''
                        "
                      >
                        {{
                          showApouForStu[student.id] && student.sumap ? "&#10134;" : "&#128065;"
                        }}
                      </button>
                      <button
                        @click="editApousies(student)"
                        class="bg-blue-200 px-0.5 border rounded"
                        :title="
                          'Εισαγωγή απουσιών ' +
                          student.eponimo +
                          ' ' +
                          student.onoma
                        "
                      >
                        &#10133;
                      </button>
                      <button
                        @click="editStudent(student)"
                        class="bg-yellow-200 px-0.5 border rounded"
                        :title="
                          'Επεξεργασία ' + student.eponimo + ' ' + student.onoma
                        "
                      >
                        &#128395;
                      </button>
                      <button
                        @click="deleteStudent(student)"
                        class="bg-red-200 px-1 border rounded"
                        :title="
                          'Διαγραφή ' + student.eponimo + ' ' + student.onoma
                        "
                      >
                        &#128465;
                      </button>
                    </td>
                  </tr>
                  <tr
                    v-show="showApouForStu[student.id] && student.sumap"
                    class="flex flex-col flex-no wrap sm:table-row bg-blue-100"
                  >
                    <td :colspan="tableLabels.length" class="text-center">
                      <!-- ΤΑΒΛΕ ΑΠΟΥΣΙΕΣ -->
                      <table
                        class="
                          w-11/12
                          sm:w-10/12
                          bg-white
                          rounded-lg
                          overflow-hidden
                          sm:shadow-lg
                          my-2
                          inline-table
                        "
                      >
                        <tbody class="flex-1 sm:flex-none">
                          <tr
                            class="
                              text-white
                              bg-gray-400
                              table-row
                              rounded-t-lg
                            "
                          >
                            <td
                              v-for="(label, index) in tableApouLabels"
                              :key="label"
                              class="p-1 sm:px-2 font-semibold"
                              :class="{
                                'hidden sm:table-cell':
                                  index > 3 &&
                                  index < tableApouLabels.length - 1,
                              }"
                            >
                              {{ label }}
                            </td>
                          </tr>
                          <template
                            v-for="apousies in apousiesForStudent[student.id]"
                            :key="apousies.id"
                          >
                            <tr class="table-row hover:bg-gray-100 text-sm">
                              <td
                                rowspan="2"
                                class="
                                  border-grey-light border
                                  p-1
                                  text-center
                                  sm:hidden
                                "
                              >
                                {{ apousies.aa }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  px-2
                                  text-center
                                  hidden
                                  sm:table-cell
                                "
                              >
                                {{ apousies.aa }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  p-1
                                  sm:px-2
                                  text-center
                                "
                              >
                                {{ apousies.tot }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  p-1
                                  sm:px-2
                                  text-center
                                "
                              >
                                {{ apousies.dateShow }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  p-1
                                  sm:px-2
                                  text-center
                                  font-bold
                                "
                              >
                                {{
                                  apousies.sum == 0 ? "&nbsp;" : apousies.sum
                                }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  p-1
                                  sm:px-2
                                  text-center
                                  font-bold
                                  hidden
                                  sm:table-cell
                                "
                              >
                                {{
                                  apousies.sumapov == 0 ? "&nbsp;" : apousies.sumapov
                                }}
                              </td>
                              <td
                                v-for="(day, index) in apousies.arrApou['apou']"
                                :key="index"
                                class="
                                  border-gray-500 border border-l-2 border-r-2
                                  p-1
                                  sm:px-2
                                  text-center
                                  hidden
                                  sm:table-cell
                                "
                                :class="{
                                  'bg-red-100':apousies.arrApou.apov[index]
                                }"
                                :title="arrNames[apousies.arrApou.teach[index]]"
                              >
                                {{ day ? "+" : "&nbsp;" }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  p-1
                                  sm:px-2
                                  text-center
                                  space-x-2
                                "
                              >
                                <button
                                  @click="editApousies(student, apousies)"
                                  class="bg-yellow-200 px-0.5 border rounded"
                                  :title="
                                    'Επεξεργασία απουσιών για ' +
                                    apousies.dateShow
                                  "
                                >
                                  &#128395;
                                </button>
                                <button
                                  @click="deleteApousies(student, apousies)"
                                  class="bg-red-200 px-1 border rounded"
                                  :title="
                                    'Διαγραφή απουσιών για ' + apousies.dateShow
                                  "
                                >
                                  &#128465;
                                </button>
                              </td>
                            </tr>
                            <tr class="sm:hidden hover:bg-gray-100">
                              <td colspan="5" class="border text-xs">
                                <table class="w-full">
                                  <tr>
                                    <td class="w-16 text-right">ώρες:</td>

                                    <td
                                      v-for="(chk, index) in apousies.arrApou.apou"
                                      :key="index"
                                      class="w-4"
                                      :class="{
                                        'opacity-20': !chk,
                                        'font-medium': chk,
                                        'bg-red-200':apousies.arrApou.apov[index]
                                      }"
                                    >
                                      {{ index + "η" }}
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </template>
                        </tbody>
                      </table>
                      <!-- ΤΑΒΛΕ ΑΠΟΥΣΙΕΣ ΤΕΛΟΣ -->
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>

            <Pagination
              class="mx-auto pt-4"
              :links="students.links"
              :queryStr="queryStr"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>

  <!-- MODAL STUDENT -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="stuIsOpen.open"
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
          {{ stuEditMode ? "Επεξεργασία" : "Εισαγωγή" }} μαθητή
        </div>
        <div v-show="errMsg.msg" class="text-red-500 mx-4 p-2 text-center">
          {{ errMsg.msg }}
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
          <span class="sm:col-start-3">Α.Μ.</span>
          <BreezeInput
            v-model="stuForm.id"
            class="w-full p-1 text-center"
            :class="{
              border: !stuEditMode,
              'select-none': stuEditMode,
            }"
          />
          <span class="sm:col-span-2">&nbsp;</span>
          <span>Επώνυμο</span>
          <BreezeInput
            v-model="stuForm.eponimo"
            class="w-full p-1 col-span-2 border"
          />
          <span>Όνομα</span>
          <BreezeInput
            v-model="stuForm.onoma"
            class="w-full p-1 col-span-2 border"
          />
          <span>Πατρώνυμο</span>
          <BreezeInput
            v-model="stuForm.patronimo"
            class="w-full p-1 col-span-2 border"
          />
          <span>Email</span>
          <BreezeInput
            v-model="stuForm.email"
            class="w-full p-1 col-span-2 border"
          />
          <template v-for="index in tmimataRows" :key="index">
            <span>Τμήμα</span>
            <BreezeInput
              v-model="stuForm.tmima[(index - 1) * 2]"
              class="w-full p-1 col-span-2 border"
            />
            <span>Τμήμα</span>
            <BreezeInput
              v-model="stuForm.tmima[(index - 1) * 2 + 1]"
              class="w-full p-1 col-span-2 border"
            />
          </template>
        </div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 text-right space-x-2">
          <button
            @click="submitStuForm"
            type="button"
            :disabled="!stuForm.isDirty"
            class="gthButton disabled:opacity-50"
            :class="{ 'cursor-not-allowed': !stuForm.isDirty }"
          >
            Αποθήκευση
          </button>
          <button
            @click="stuIsOpen.open = false"
            type="button"
            class="gthButton"
          >
            Άκυρο
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL STUDENT ΤΕΛΟΣ-->
  <!-- MODAL APOUSIES -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="apouIsOpen"
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
          {{ apouEditMode ? "Επεξεργασία" : "Εισαγωγή" }} απουσιών
        </div>
        <div
          v-show="errMsg.msg"
          class="text-red-500 mx-4 mt-2 text-center"
        >
          {{ errMsg.msg }}
        </div>
        <div class="font-semibold text-xl mx-4 mt-2 text-center">
          {{ stuForApousies }}
        </div>
        <div class="flex bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 justify-evenly">
          <div class="flex">
            <BreezeInput
              v-model="apouForm.date"
              type="date"
              :disabled="apouEditMode"
              class="w-max p-1 text-center"
              :class="{
                border: !apouEditMode,
                'select-none': apouEditMode,
              }"
            />
          </div>
          <div class="flex bg-gray-100 space-x-1 pb-1 mx-2 rounded-lg">
            <div
              v-for="index in totalHours"
              :key="index"
              class="text-gray-400 sm:px-1"
            >
              <div class="flex flex-col"
               :class="{
                  'bg-red-200':apouForm.apov[index]
                }"
                :title="arrNames[apouForm.teach[index]]"
              >
                <span>{{ index }}η</span>
                <BreezeCheckbox
                  class="ml-0.5 bg-white"
                  v-model="apouForm.apou[index]"
                  :checked="apouForm.apou[index]"
                  @click="toggleApousia(index)"
 
                />

                    <!-- κουμπί αποβολής -->
                    <div 
                      class="h-2 bg-gray-300 mt-1 cursor-pointer rounded" 
                      :class="{
                          'bg-red-400':apouForm.apov[index]
                        }"
                      title="Αποβολή" 
                      @click="toggleApovoli(index)"
                    />

              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 text-right space-x-2">
          <button
            @click="submitApouForm"
            type="button"
            :disabled="!apouForm.isDirty"
            class="gthButton disabled:opacity-50"
            :class="{ 'cursor-not-allowed': !apouForm.isDirty }"
          >
            Αποθήκευση
          </button>
          <button
            @click="apouIsOpen = false"
            type="button"
            class="gthButton"
          >
            Άκυρο
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL APOUSIES ΤΕΛΟΣ-->
</template>

<style scoped>
@reference "tailwindcss";
.gthButton {
  @apply bg-gray-100  hover:bg-gray-300  active:bg-gray-500  text-gray-700  hover:text-gray-900  active:text-gray-100
        text-sm  font-semibold  py-1 px-2 border border-gray-300  hover:border-transparent rounded-md;
}
</style>
