<template>
  <div class="edit_container">
    <div v-loading="imageLoading" element-loading-text="请稍等，图片上传中">
      <quill-editor
        v-model="content"
        class="page-editor"
        ref="myQuillEditor"
        :options="editorOption"
        @change="editorChange"
      ></quill-editor>
      <input
        style="display: none"
        id="imgInput"
        type="file"
        name="avator"
        multiple
        accept="image/jpg, image/jpeg, image/png, image/gif"
        @change="handleUpload"
      />
    </div>
  </div>
</template>

<script>
import Quill from "quill";

export default {
  name: "editor",
  props: {
    value: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      hostURL: "https://apihango.hangomart.com",
      uploadURL: "https://apihango.hangomart.com/goods/image_upload",
      pageForm: {
        intro: "",
        page: "",
        language: ""
      },
      imageLoading: false,
      addImgRange: "",
      qq_cloud_config: {},
      editorOption: {
        placeholder: ''
      }
    };
  },
  computed: {
    content: {
      get() {
        return this.value;
      },
      set(newValue) {
        if (newValue == null || newValue.length === 0) {
          return;
        }

        this.$emit("input", newValue);
      }
    },
    editor() {
      return this.$refs.myQuillEditor.quill;
    }
  },
  mounted() {
    var self = this;

    var imgHandler = async function(image) {
      self.addImgRange = self.$refs.myQuillEditor.quill.getSelection();
      if (image) {
        var fileInput = document.getElementById("imgInput");
        fileInput.click();
      }
    };
    self.$refs.myQuillEditor.quill
      .getModule("toolbar")
      .addHandler("image", imgHandler);
  },
  methods: {
    onEditorReady(editor) {
      // 准备编辑器
    },
    onEditorBlur() {}, // 失去焦点事件
    onEditorFocus() {}, // 获得焦点事件
    onEditorChange(event) {}, // 内容改变事件
    saveHtml: function(event) {
      if (this.content == "") {
        this.$message.error("请输入详情后再保存");
        return;
      }

      this.$emit("input", this.content);
    },
    handleUpload(e) {
      var self = this;
      var files = e.target.files;
      if (files.length == 0) return;
      var pic_list = [];
      Object.keys(files).forEach(key => {
        const isLt500K = files[key].size / 1024 < 1024;
        if (!isLt500K) {
          this.$message.warning("上传图片大小不能超过 1M!");
          return;
        } else {
          pic_list.push(files[key]);
        }
      });
      if (pic_list.length > 0) {
        var is_uploaded = 0;
        self.imageLoading = true;

        for (let i = 0; i < pic_list.length; i++) {
          let file = pic_list[i];
          let url = "";
          var reader = new FileReader();
          reader.readAsDataURL(file);
          let that = this;
          reader.onloadend = function(e) {
            url = this.result.substring(this.result.indexOf(",") + 1);
          };

          let formData = new FormData();
          formData.append("file", file);
          formData.append("access_token", 'that.GLOBAL.access_token');
          that.$axios
            .post(that.uploadURL, formData)
            .then(function(res) {
              is_uploaded++;
              if (res.data.code == 200) {
                var url = that.hostURL + res.data.data.image_url;
                that.addImgRange = that.$refs.myQuillEditor.quill.getSelection();
                that.$refs.myQuillEditor.quill.insertEmbed(
                  that.addImgRange != null ? that.addImgRange.index : 0,
                  "image",
                  url,
                  Quill.sources.USER
                );

                that.$refs.myQuillEditor.quill.setSelection(is_uploaded + 1);
              } else {
                that.$message.error(res.data.datas.error);
              }

              if (is_uploaded == pic_list.length) {
                that.imageLoading = false;
                document.getElementById("imgInput").value = "";
              }
            })
            .catch(function(error) {
              is_uploaded++;
              // console.log(error);
            });
        }
      }
    },
    submitForm(formName) {},
    editorChange({ editor, html, text }) {
      this.$emit("editorChange", html);
    }
  },
  modules: {
    toolbar: [
      ["bold", "italic", "underline", "strike"], //加粗，斜体，下划线，删除线
      ["blockquote", "code-block"], //引用，代码块

      [{ header: 1 }, { header: 2 }], // 标题，键值对的形式；1、2表示字体大小
      [{ list: "ordered" }, { list: "bullet" }], //列表
      [{ script: "sub" }, { script: "super" }], // 上下标
      [{ indent: "-1" }, { indent: "+1" }], // 缩进
      [{ direction: "rtl" }], // 文本方向

      [{ size: ["small", false, "large", "huge"] }], // 字体大小
      [{ header: [1, 2, 3, 4, 5, 6, false] }], //几级标题

      [{ color: [] }, { background: [] }], // 字体颜色，字体背景颜色
      [{ font: [] }], //字体
      [{ align: [] }], //对齐方式

      ["clean"], //清除字体样式
      ["image"] //上传图片、上传视频
    ]
  }
};
</script>

<style>
.edit_container {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
  margin-bottom: 60px;
}
.page-editor {
  width: 1000px;
  height: 400px;
  background: #fff;
}
.ql-container {
  background: #fff !important;
}
</style>

<style scoped>
.save {
  border: none;
  width: 120px;
  height: 40px;
  line-height: 40px;
  text-align: center;
  color: #fff;
  font-size: 14px;
  background: #00b944;
  border-radius: 4px;
  margin: 20px auto;
}
</style>
