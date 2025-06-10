<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import SettingsPanel from "@/components/SettingsPanel.vue";
import BreezeInput from "@/components/ui/input/Input.vue";
import { ref } from "vue";
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps<{
  activeGradePeriod: String;
  token: String;
  }>();

    const apo = ref("");
    const eos = ref("");
    const date = new Date(Date.now()).toLocaleString().split(",")[0];
    const exportApousUrl = computed(function () {
      let url = "exportApouxls";
      if (apo.value || eos.value) url += "?";
      if (apo.value) url += "apoDate=" + apo.value;
      if (apo.value && eos.value) url += "&";
      if (eos.value) url += "eosDate=" + eos.value;
      return url;
    });
    const gradesForm = useForm({
      xls: null,
      rowLabels: 3,
      amColumn: 2,
      lessonColumn: 5,
    });

</script>

<template>
  <Head title="Εξαγωγή xls" />

  <AppLayout>

    <div class="px-4 pt-4 w-full max-w-7xl mx-auto ">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div
            class="p-6 bg-white border-b border-gray-200 flex justify-between"
          >
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              Εξαγωγή&nbsp;xls
            </h2>
          </div>
        </div>
      </div>

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
            <SettingsPanel title="Εξαγωγή απουσιών για τις ημερομηνίες">
              <div class="py-2 text-center">
                κενές ημ/νιες = σήμερα: {{ date }}
              </div>
              <div
                class="
                  flex flex-col
                  space-y-1
                  text-center
                  sm:space-y-0 sm:flex-row sm:justify-between sm:space-x-1
                "
              >
                <div>
                  από
                  <BreezeInput v-model="apo" type="date" class="py-1" />
                </div>
                <div>
                  έως
                  <BreezeInput v-model="eos" type="date" class="py-1" />
                </div>
                <a :href="exportApousUrl" as="button" type="button" class="gth">
                  Εξαγωγή_xls
                </a>
              </div>
            </SettingsPanel>

            <SettingsPanel
              title="Εξαγωγή αρχείου 187.xls όλων των μαθητών όλων των τάξεων για καταχώριση στο myschool"
            >
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
                  text-center
                  sm:space-y-0 sm:flex-row sm:justify-between sm:space-x-1
                "
              >
                <div>Βαθμολογική περίοδος:</div>
                <div>{{ activeGradePeriod }}</div>
                <a :href="route('gradesXls')" class="gth"> Εξαγωγή_xls </a>
              </div>
            </SettingsPanel>
            <SettingsPanel
              title="Ενημέρωση των εξηχθέντων αρχείων 187.xls για κάθε τάξη από το myschool"
            >
              <div v-show="!activeGradePeriod" class="text-center">
                Επιλέξτε βαθμολογική περίοδο στις
                <Link :href="route('settings')" class="underline"
                  >ρυθμίσεις</Link
                >
              </div>
              <form
                name="frm"
                id="frm"
                role="form"
                method="POST"
                :action="route('populateGradesXls')"
                enctype="multipart/form-data"
              >
                <input type="hidden" name="_token" :value="token" />
                <div
                  v-show="activeGradePeriod"
                  class="
                    flex flex-col
                    space-y-1
                    text-center
                    sm:space-y-0 sm:flex-row sm:justify-between sm:space-x-1
                  "
                >
                  <div>
                    {{ activeGradePeriod }}
                  </div>
                  <input
                    type="file"
                    class="gthFile"
                    name="xls"
                    @input="gradesForm.xls = $event.target.files[0]"
                  />
                  <button
                    type="submit"
                    class="gth"
                    :disabled="
                      !gradesForm.xls ||
                      gradesForm.processing ||
                      !activeGradePeriod
                    "
                  >
                    Ενημέρωση_xls
                  </button>
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
              </form>
            </SettingsPanel>
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
        text-sm  font-semibold  py-1 px-4 border border-gray-300  hover:border-transparent rounded-md self-center disabled:opacity-50;
}
.gthFile {
  @apply w-full sm:w-2/5 md:w-1/2 p-0.5 text-gray-700  text-sm  font-semibold  border border-gray-300  hover:border-transparent rounded-md self-center;
}
</style>
