<template>
  <n-space vertical size="large" style="width: 100%; padding-top: 30px;" @keydown="handleKeydown" tabindex="0">
    <n-form ref="formRef" :model="formValue" label-placement="left">
      <n-grid :cols="2">
        <n-grid-item style="padding-right: 10px;">
          <n-scrollbar style="max-height: 300px;">
            <template v-if="loading">
              <n-skeleton height="40px" text :repeat="5" style="margin-bottom: 8px;" :sharp="false"/>
            </template>
            <template v-else>
              <draggable v-model="formValue.availableGroups" class="list-group" group="shared" item-key="id">
                <template #item="{ element }">
                  <div class="list-group-item" :key="element.id">
                    {{ element.title }}
                  </div>
                </template>
              </draggable>
            </template>
          </n-scrollbar>
        </n-grid-item>
        <n-grid-item style="padding-left: 10px;">
          <n-scrollbar style="max-height: 300px;">
            <draggable v-model="formValue.selectedGroups" class="list-group" group="shared" item-key="id">
              <template #item="{ element }">
                <div class="list-group-item" :key="element.id">
                  {{ element.title }}
                </div>
              </template>
            </draggable>
          </n-scrollbar>
        </n-grid-item>
      </n-grid>
      <n-grid :cols="2" style="padding-top: 30px;">
        <n-grid-item style="padding-right: 10px;">
          <n-form-item label="Mailing List Name">
            <n-input v-model:value="formValue.groupName" placeholder="Enter mailing list name"/>
          </n-form-item>
        </n-grid-item>
      </n-grid>
      <n-grid :cols="1">
        <n-grid-item>
          <n-space align="center" justify="end">
            <n-button @click="cancelGroupEdit">Cancel</n-button>
            <n-button type="primary" @click="saveGroup">Save</n-button>
          </n-space>
        </n-grid-item>
      </n-grid>
    </n-form>
  </n-space>
</template>


<script>
import {onMounted, reactive, ref, toRefs} from 'vue';
import {
  NButton,
  NForm,
  NFormItem,
  NGrid,
  NGridItem,
  NInput,
  NScrollbar,
  NSkeleton,
  NSpace,
  useLoadingBar,
  useMessage
} from 'naive-ui';
import draggable from 'vuedraggable';
import {useUserGroupStore} from "../stores/mailinglist/userGroupStore";
import {useMailingListStore} from "../stores/mailinglist/mailinglistStore";

export default {
  name: 'GroupEditorDialog',
  components: {
    NForm,
    NFormItem,
    NInput,
    NSpace,
    NButton,
    NGrid,
    NGridItem,
    NScrollbar,
    NSkeleton,
    draggable
  },
  props: {
    id: {
      type: Number,
      default: 0,
      required: false
    },
    onClose: {
      type: Function,
      required: true
    }
  },

  setup(props) {
    const formRef = ref(null);
    const formValue = reactive({
      groupName: '',
      availableGroups: [],
      selectedGroups: []
    });
    const userGroupStore = useUserGroupStore();
    const mailingListStore = useMailingListStore();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const loading = ref(true);

    onMounted(async () => {
      await userGroupStore.getList(msgPopup, loadingBar);
      if (props.id > -1 ) {
        const entryDetails = await mailingListStore.getDetails(props.id, msgPopup, loadingBar, true);
        formValue.groupName = entryDetails.name;
        formValue.selectedGroups = entryDetails.groups.map(group => ({
          ...group,
          title: group.title
        }));

        formValue.availableGroups = userGroupStore.documentsPage.docs.filter(doc =>
            !formValue.selectedGroups.some(selectedGroup => selectedGroup.id === doc.id)
        );
      } else {
        formValue.availableGroups = userGroupStore.documentsPage.docs;
      }
      loading.value = false;
    });

    const validateForm = () => {
      const errors = {};

      if (!formValue.groupName) {
        errors.groupName = 'Mailing list name cannot be empty';
      }

      if (!formValue.selectedGroups || formValue.selectedGroups.length === 0) {
        errors.selectedGroups = 'At least one group should be selected';
      }

      return Object.keys(errors).length > 0 ? errors : null;
    };

    const saveGroup = async () => {
      const errors = validateForm();
      if (errors) {
        Object.keys(errors).forEach(fieldName => {
          msgPopup.error(errors[fieldName], {
            closable: true,
            duration: 10000
          });
        });
      } else {
        try {
          await mailingListStore.saveList(formValue, props.id, msgPopup, loadingBar);
          formValue.groupName = '';
          formValue.selectedGroups = [];
          props.onClose(true);
        } catch (e) {
          msgPopup.error(e.message, {
            closable: true,
            duration: 10000
          });
        }
      }
    };

    const cancelGroupEdit = () => {
      formValue.groupName = '';
      formValue.selectedGroups = [];
      formValue.availableGroups = userGroupStore.documentsPage.docs;
      props.onClose(false);
    };

    const handleKeydown = (event) => {
      if (event.key === 'Enter') {
        saveGroup();
      } else if (event.key === 'Escape') {
        cancelGroupEdit();
      }
    };

    return {
      formValue,
      formRef,
      saveGroup,
      cancelGroupEdit,
      loading,
      handleKeydown
    };
  }
};
</script>
