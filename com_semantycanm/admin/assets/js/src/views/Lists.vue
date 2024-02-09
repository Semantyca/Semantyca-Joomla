<template>
  <n-form inline ref="formRef" :rules="rules" :model="formValue">
    <div class="container mt-3">
      <div class="row">
        <div class="col">
          <h3>{{ store.translations.AVAILABLE_USER_GROUPS }}</h3>
          <draggable v-model="formValue.availableGroups" class="list-group" group="shared" itemKey="id">
            <template #item="{ element }">
              <div class="list-group-item" :key="element.id">
                {{ element.title }}
              </div>
            </template>
          </draggable>
        </div>
        <div class="col">
          <h3>{{ store.translations.SELECTED_USER_GROUPS }}</h3>
          <draggable v-model="formValue.selectedGroups" class="list-group" group="shared" itemKey="id">
            <template #item="{ element }">
              <div class="list-group-item" :key="element.id">
                {{ element.title }}
              </div>
            </template>
          </draggable>
          <!--          </n-form-item-gi>-->
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-3 d-flex align-items-center">
          <n-form-item label="Mailing List Name" path="groupName" class="w-100">
            <n-input v-model:value="formValue.groupName"
                     style="width: 100%"
                     id="mailingListName"
                     type="text"
                     placeholder="Mailing List Name"/>
          </n-form-item>
        </div>
        <div class="col  d-flex align-items-center">
          <n-button-group>
            <n-button id="saveGroup"
                      type="primary"
                      @click="validateAndSave">{{ store.translations.SAVE }}
            </n-button>
            <n-button id="cancelEditing"
                      type="success"
                      @click="cancelList">{{ store.translations.CANCEL }}
            </n-button>
          </n-button-group>
        </div>
      </div>
      <div class="row mt-3">
        <div class="col">
          <h3>{{ store.translations.MAILING_LISTS }}</h3>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <n-data-table
              :columns="columns"
              :data="mailingListStore.mailingListDocsView.docs"
              :pagination="pagination"
          />
          <input type="hidden" id="mailingListMode" value=""/>
        </div>
      </div>
    </div>
  </n-form>

</template>

<script>
import {onMounted, reactive, ref} from 'vue';
import {
  NButton,
  NButtonGroup,
  NDataTable,
  NForm,
  NFormItem,
  NFormItemGi,
  NGrid,
  NGridItem,
  NInput,
  NSpace,
  useMessage
} from "naive-ui";
import {useUserGroupStore} from "../stores/userGroupStore";
import {useMailingListStore} from "../stores/mailinglistStore";
import {useGlobalStore} from "../stores/globalStore";
import draggable from 'vuedraggable';

export default {
  name: 'Lists',
  components: {
    NGrid,
    NGridItem,
    NButton,
    NButtonGroup,
    NSpace,
    NInput,
    NDataTable,
    NForm,
    NFormItem,
    NFormItemGi,
    draggable
  },
  setup() {
    const formRef = ref(null);
    const store = useGlobalStore();
    const userGroupStore = useUserGroupStore();
    const mailingListStore = useMailingListStore();
    const message = useMessage();

    const state = reactive({
      mailingListMode: ''
    });

    const validateAndSave = (e) => {
      e.preventDefault();

      const selectedGroupsValidation = rules.selectedGroups.validator(null, formValue.value.selectedGroups);
      if (!selectedGroupsValidation) {
        message.error(rules.selectedGroups.message);
        return;
      }

      formRef.value.validate((errors) => {
        if (!errors) {
          userGroupStore.saveList(formRef.value.model, '').then(() => {
            state.mailingListMode = '';
            formValue.value.groupName = '';
            formValue.value.selectedGroups = [];
          }).catch((error) => {
            console.error("Error saving the list:", error);
            message.error("Failed to save the list.");
          });
        } else {
          Object.keys(errors).forEach(fieldName => {
            const fieldError = errors[fieldName];
            if (fieldError && fieldError.length > 0) {
              message.error(fieldError[0].message);
            }
          });
        }
      });
    };

    const cancelList = () => {
      formValue.value.groupName = '';
      formValue.value.selectedGroups = [];
      formValue.value.availableGroups = userGroupStore.documentsPage.docs;
      state.mailingListMode = '';
    };

    const rules = {
      groupName: {
        required: true,
        message: 'Mailing list name cannot be empty'
      },
      selectedGroups: {
        validator(rule, value) {
          return value && value.length > 0;
        },
        message: 'At least one group should be selected'
      }
    };

    const formValue = ref({
      groupName: '',
      availableGroups: [],
      selectedGroups: []
    });

    const createColumns = () => {
      return [
        {
          title: 'Name',
          key: 'name'
        },
        {
          title: 'Created',
          key: 'reg_date'
        }
      ]
    }

    const paginationReactive = reactive({
      page: 1,
      pageSize: 5,
      onChange: (page) => {
        paginationReactive.page = page;
      },
      onUpdatePageSize: (pageSize) => {
        paginationReactive.pageSize = pageSize;
        paginationReactive.page = 1;
      }
    });

    onMounted(async () => {
      await userGroupStore.getList();
      await mailingListStore.refreshMailingList(1);
      formValue.value.availableGroups = userGroupStore.documentsPage.docs;
    });

    return {
      state,
      store,
      userGroupStore,
      mailingListStore,
      cancelList,
      validateAndSave,
      rules,
      columns: createColumns(),
      pagination: paginationReactive,
      formValue,
      formRef,
      message
    };
  }
};
</script>
