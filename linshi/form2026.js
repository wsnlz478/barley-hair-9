/**
 * ============================================
 * 植发咨询表单 - 全端兼容增强版
 * 升级：支持多类型联系方式（手机/微信/WhatsApp/邮箱）
 * 新增：繁体中文语言支持
 * 优化：配置开关置顶+详细注释，代码结构精简
 * ============================================
 */

// ===================== 全局配置开关（置顶，可直接修改） =====================
// 【基础配置】
const ZF_FORM_GLOBAL_CONFIG = {
    // 接口配置
    api: "https://www.zhifazhifa.com/sendemail/send2026.php", // 表单提交后端接口
    wechat: "15810155700", // 客服微信号
    
    // 二维码配置
    qrcode: {
        enable: false, // 是否显示二维码（true=显示/false=隐藏）
        src: "" // 二维码图片地址（enable=true时必填）
    },

    // 显示配置
    display: {
        pcFloat: true, // PC端是否悬浮显示（true=悬浮/false=静态）
        mobileMode: 'full', // 移动端显示模式（'full'=全屏/'mini'=迷你/'wechat'=仅微信）
        pcRememberCollapse: true // PC端是否记忆表单关闭状态（true=记忆/false=不记忆）
    },

    // 微信号显示配置
    wechatRow: {
        enable: true // 是否显示微信号行（true=显示/false=隐藏）
    },

    // 多语言配置
    language: {
        enable: true, // 是否启用多语言（true=启用/false=禁用）
        showSwitch: true, // 是否显示语言切换按钮（true=显示/false=隐藏）
        default: "zh" // 默认语言（'zh'=简体中文/'zh-tw'=繁体中文/'en'=英文）
    }
};

// ===================== 多语言配置（简体/繁体/英文） =====================
const I18N = {
    // 简体中文
    zh: {
        contactPlaceholder: "手机/微信/WhatsApp *", // 联系方式提示文字
        nicknamePlaceholder: "您的昵称 *",
        project: { label: ["发际线种植","头发加密","秃顶植发","眉毛种植","胡须种植","鬓角种植","体毛种植","脱发治疗","头发养护"] },
        messagePlaceholder: "留言内容（选填）",
        submitBtn: "立即提交",
        submitting: "提交中...",
        tips: { 
            nicknameEmpty: "请填写昵称", 
            contactEmpty: "请填写联系方式", // 替换原手机号验证提示
            networkError: "网络异常，请重试", 
            success: "提交成功，我们将尽快联系您！" 
        },
        qrcodeText: "扫码添加微信咨询", 
        wechatLabel: "微信：", 
        copyBtn: "复制", 
        copyToast: "微信号复制成功", 
        closeBtn: "关闭", 
        expandBtn: "咨询", 
        langSwitchZH: "繁", // 切换到繁体
        langSwitchEN: "EN", // 切换到英文
        langSwitchTW: "简"  // 切换到简体
    },
    // 繁体中文
    "zh-tw": {
        contactPlaceholder: "手機/微信/WhatsApp *",
        nicknamePlaceholder: "您的暱稱 *",
        project: { label: ["髮際線種植","頭髮加密","禿頂植髮","眉毛種植","鬍鬚種植","鬢角種植","體毛種植","脫髮治療","頭髮養護"] },
        messagePlaceholder: "留言內容（選填）",
        submitBtn: "立即提交",
        submitting: "提交中...",
        tips: { 
            nicknameEmpty: "請填寫暱稱", 
            contactEmpty: "請填寫聯繫方式", 
            networkError: "網路異常，請重試", 
            success: "提交成功，我們將儘快聯繫您！" 
        },
        qrcodeText: "掃碼添加微信諮詢", 
        wechatLabel: "微信：", 
        copyBtn: "複製", 
        copyToast: "微信號複製成功", 
        closeBtn: "關閉", 
        expandBtn: "諮詢", 
        langSwitchZH: "简",
        langSwitchEN: "EN",
        langSwitchTW: "繁"
    },
    // 英文
    en: {
        contactPlaceholder: "Phone/WeChat/WhatsApp *",
        nicknamePlaceholder: "Your Nickname *",
        project: { label: ["Hairline","Density","Baldness","Eyebrow","Beard","Sideburn","Body Hair","Hair Loss Treatment","Hair Care"] },
        messagePlaceholder: "Message (Optional)",
        submitBtn: "Submit Now",
        submitting: "Submitting...",
        tips: { 
            nicknameEmpty: "Please enter nickname", 
            contactEmpty: "Please enter contact information", 
            networkError: "Network error, please retry", 
            success: "Submitted successfully! We'll contact you soon." 
        },
        qrcodeText: "Scan QR code for WeChat", 
        wechatLabel: "WeChat: ", 
        copyBtn: "Copy", 
        copyToast: "WeChat ID copied", 
        closeBtn: "Close", 
        expandBtn: "Consult", 
        langSwitchZH: "简",
        langSwitchEN: "繁",
        langSwitchTW: "EN"
    }
};

// ===================== 核心逻辑 =====================
(function() {
    'use strict';
    let CONFIG = {}, currentLang = "zh", submitting = false, isCollapsed = false, isMobileCollapsed = false;
    let formBox = null, collapseBtn = null, mobileCollapseBtn = null, isInputFocus = false, resizeTimer = null;

    // 合并默认配置和全局配置
    function mergeConfig(defaults, user) {
        const merged = {...defaults};
        for (const key in user) {
            merged[key] = user[key] && typeof user[key] === 'object' && !Array.isArray(user[key]) 
                ? mergeConfig(defaults[key], user[key]) 
                : user[key];
        }
        return merged;
    }

    // 安全转义文本
    function safeText(str) { 
        return str.replace(/[&<>"']/g, c => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[c])); 
    }

    // 显示提示弹窗
    function showToast(text) {
        let toast = document.getElementById('zf-toast') || document.createElement('div');
        toast.id = 'zf-toast'; 
        document.body.appendChild(toast);
        toast.innerText = text; 
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // 复制微信号
    async function copyWechat() {
        try { 
            await navigator.clipboard.writeText(CONFIG.wechat); 
            showToast(I18N[currentLang].copyToast); 
        } catch (e) { 
            const i = document.createElement('input');
            i.value = CONFIG.wechat;
            document.body.appendChild(i);
            i.select();
            document.execCommand('copy');
            document.body.removeChild(i);
            showToast(I18N[currentLang].copyToast); 
        }
    }

    // 注入样式
    function injectStyles() {
        const s = document.createElement('style');
        s.textContent = `
            *{box-sizing:border-box;margin:0;padding:0}
            .zf-form-box{font-family:"Microsoft YaHei",sans-serif;background:#fff;border-radius:18px;box-shadow:0 4px 20px rgba(0,0,0,.12);padding:20px;transition:.3s}
            @media (min-width:769px){
                .zf-pc-float{position:fixed;right:15px;top:50%;transform:translateY(-50%);width:340px;z-index:9999}
                .zf-pc-static{max-width:420px;margin:20px auto}
                .zf-collapse-btn{position:fixed;right:0;top:50%;transform:translateY(-50%);width:35px;height:90px;background:linear-gradient(135deg,#2b85e4,#1a6cc7);border-radius:45px 0 0 45px;box-shadow:-4px 0 15px rgba(43,133,228,.4);cursor:pointer;z-index:9999;display:flex;align-items:center;justify-content:center;writing-mode:vertical-rl;color:#fff;font-size:14px;font-weight:700;border:none}
            }
            @media (max-width:768px){
                .zf-pc-float,.zf-pc-static,.zf-qrcode-box,.zf-collapse-btn{display:none!important}
                .zf-mobile-full{position:fixed;bottom:0;left:0;width:100%;max-height:45vh;z-index:9999;border-radius:18px 18px 0 0;overflow-y:auto}
                .zf-mobile-full-collapse-btn{position:fixed;bottom:20px;left:20px;width:60px;height:60px;background:linear-gradient(135deg,#2b85e4,#1a6cc7);border-radius:50%;box-shadow:0 4px 15px rgba(43,133,228,.4);cursor:pointer;z-index:9999;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;border:none}
                .zf-mobile-mini{position:fixed;bottom:0;left:0;width:100%;z-index:9999;border-radius:18px 18px 0 0;padding:15px 20px;display:flex;gap:10px;align-items:center}
                .zf-mobile-mini .zf-input{flex:1;margin:0;height:44px}
                .zf-mobile-mini .zf-submit-btn{width:auto;padding:0 20px;height:44px;margin:0;font-size:14px}
                .zf-mobile-wechat{position:fixed;bottom:0;left:0;width:100%;z-index:9999;border-radius:18px 18px 0 0;padding:20px;display:flex;justify-content:space-between;align-items:center}
                .zf-mobile-wechat .zf-wechat-text{font-size:16px;color:#333}
                .zf-mobile-wechat .zf-copy-btn{padding:8px 20px;font-size:14px}
            }
            .zf-input{width:100%;height:48px;padding:0 14px;margin:10px 0;border:1px solid #e8e8e8;border-radius:12px;font-size:15px;transition:.2s}
            .zf-input:focus{outline:none;border-color:#2b85e4}
            .zf-textarea{height:80px;padding-top:12px;resize:none}
            .zf-radio-group{margin:12px 0;display:flex;flex-wrap:wrap;gap:10px;font-size:13px}
            .zf-radio-group label{padding:6px 12px;background:#f8f9fa;border-radius:8px;cursor:pointer;transition:.2s;border:1px solid transparent}
            .zf-radio-group label:has(input:checked){background:#e6f4ff;border-color:#2b85e4;color:#2b85e4}
            .zf-radio-group input{display:none}
            .zf-submit-btn{width:100%;height:48px;border:none;border-radius:12px;background:linear-gradient(135deg,#2b85e4,#1a6cc7);color:#fff;font-size:16px;font-weight:700;cursor:pointer;margin-top:8px;transition:.2s}
            .zf-submit-btn:disabled{background:#ccc;cursor:not-allowed}
            .zf-submit-btn:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 4px 12px rgba(43,133,228,.3)}
            .zf-tips{text-align:center;font-size:13px;margin-top:12px;color:#666}
            .zf-qrcode-box{margin-top:18px;text-align:center;padding-top:18px;border-top:1px solid #f0f0f0}
            .zf-qrcode-box img{width:95px;height:95px;border-radius:10px;border:1px solid #f0f0f0}
            .zf-qrcode-box p{font-size:12px;margin-top:6px;color:#999}
            .zf-wechat-row{display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:14px;border-top:1px solid #f0f0f0}
            .zf-copy-btn{padding:6px 14px;border:1px solid #2b85e4;background:#fff;color:#2b85e4;border-radius:8px;font-size:13px;cursor:pointer;transition:.2s}
            .zf-copy-btn:hover{background:#2b85e4;color:#fff}
            .zf-top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
            .zf-close-btn{background:none;border:none;font-size:20px;color:#999;cursor:pointer}
            .zf-lang-switch{background:#f8f9fa;border:none;padding:4px 10px;border-radius:6px;font-size:12px;color:#666;cursor:pointer}
            #zf-toast{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,.8);color:#fff;padding:14px 28px;border-radius:12px;font-size:15px;z-index:999999;opacity:0;transition:.3s;pointer-events:none}
            #zf-toast.show{opacity:1}
            @media (min-width:769px){.zf-wechat-row-hidden{display:none!important}}
        `;
        document.head.appendChild(s);
    }

    // 获取表单样式类名
    function getFormClass() {
        const isMobile = window.innerWidth <= 768;
        const mobileMode = CONFIG.display.mobileMode;
        if(isMobile) {
            return mobileMode === 'full' ? 'zf-form-box zf-mobile-full' :
                   mobileMode === 'mini' ? 'zf-form-box zf-mobile-mini' :
                   mobileMode === 'wechat' ? 'zf-form-box zf-mobile-wechat' : '';
        }
        return CONFIG.display.pcFloat ? 'zf-form-box zf-pc-float' : 'zf-form-box zf-pc-static';
    }

    // 渲染表单
    function renderForm() {
        if(isInputFocus) return;
        // 移除旧元素
        [formBox, collapseBtn, mobileCollapseBtn].forEach(el => el && el.remove());
        
        const lang = I18N[currentLang];
        const isMobile = window.innerWidth <= 768;
        const mobileMode = CONFIG.display.mobileMode;
        
        formBox = document.createElement('div'); 
        formBox.className = getFormClass(); 
        formBox.id = 'zf-form-main';

        if(isMobile) {
            if(mobileMode === 'full') {
                isMobileCollapsed ? renderMobileCollapseBtn() : renderMobileFullForm(lang);
            } else if(mobileMode === 'mini') {
                renderMobileMiniForm(lang);
            } else if(mobileMode === 'wechat') {
                renderMobileWechatForm(lang);
            }
        } else {
            renderPCForm(lang);
        }

        if(formBox.className) {
            document.body.appendChild(formBox);
            bindEvents();
        }
    }

    // 渲染移动端全屏表单
    function renderMobileFullForm(lang) {
        let projectHtml = '<div class="zf-radio-group">';
        lang.project.label.forEach((v, i) => {
            // 关联简体中文的项目值，保证后端兼容
            projectHtml += `<label><input type="radio" name="zf-project" value="${I18N.zh.project.label[i]}" ${i===0?'checked':''}>${v}</label>`;
        });
        projectHtml += '</div>';

        formBox.innerHTML = `
            <div class="zf-top-bar">
                <span></span>
                <button class="zf-close-btn" id="zf-close-btn">×</button>
            </div>
            <input class="zf-input" id="zf-nick" placeholder="${lang.nicknamePlaceholder}">
            ${projectHtml}
            <input class="zf-input" id="zf-contact" placeholder="${lang.contactPlaceholder}">
            <textarea class="zf-input zf-textarea" id="zf-msg" placeholder="${lang.messagePlaceholder}"></textarea>
            <button class="zf-submit-btn" id="zf-submit">${lang.submitBtn}</button>
            <div class="zf-tips" id="zf-tips"></div>
        `;
    }

    // 渲染移动端迷你表单
    function renderMobileMiniForm(lang) {
        formBox.innerHTML = `
            <input class="zf-input" id="zf-contact" placeholder="${lang.contactPlaceholder}">
            <button class="zf-submit-btn" id="zf-submit">${lang.submitBtn}</button>
            <div class="zf-tips" id="zf-tips" style="display:none"></div>
        `;
    }

    // 渲染移动端仅微信表单
    function renderMobileWechatForm(lang) {
        formBox.innerHTML = `
            <span class="zf-wechat-text">${lang.wechatLabel}${CONFIG.wechat}</span>
            <button class="zf-copy-btn" onclick="window.zfCopyWechat()">${lang.copyBtn}</button>
        `;
    }

    // 渲染移动端折叠按钮
    function renderMobileCollapseBtn() {
        mobileCollapseBtn = document.createElement('button');
        mobileCollapseBtn.className = 'zf-mobile-full-collapse-btn';
        mobileCollapseBtn.innerText = I18N[currentLang].expandBtn;
        document.body.appendChild(mobileCollapseBtn);
        mobileCollapseBtn.onclick = () => {
            isMobileCollapsed = false;
            renderForm();
        };
    }

    // 渲染PC端表单
    function renderPCForm(lang) {
        let projectHtml = '<div class="zf-radio-group">';
        lang.project.label.forEach((v, i) => {
            projectHtml += `<label><input type="radio" name="zf-project" value="${I18N.zh.project.label[i]}" ${i===0?'checked':''}>${v}</label>`;
        });
        projectHtml += '</div>';

        // 二维码模块
        const qrcodeHtml = CONFIG.qrcode.enable && CONFIG.qrcode.src 
            ? `<div class="zf-qrcode-box"><img src="${CONFIG.qrcode.src}" alt="${lang.qrcodeText}"><p>${lang.qrcodeText}</p></div>` 
            : '';

        // 微信号行
        const wechatRowHtml = CONFIG.wechatRow.enable 
            ? `<div class="zf-wechat-row"><span>${lang.wechatLabel}${CONFIG.wechat}</span><button class="zf-copy-btn" onclick="window.zfCopyWechat()">${lang.copyBtn}</button></div>` 
            : '<div class="zf-wechat-row zf-wechat-row-hidden"></div>';

        // 语言切换按钮
        let langSwitchText = '';
        if(currentLang === 'zh') langSwitchText = lang.langSwitchTW;
        else if(currentLang === 'zh-tw') langSwitchText = lang.langSwitchEN;
        else langSwitchText = lang.langSwitchZH;
        
        const langSwitchHtml = CONFIG.language.enable && CONFIG.language.showSwitch 
            ? `<button class="zf-lang-switch" id="zf-lang-switch">${langSwitchText}</button>` 
            : '<span></span>';

        // 关闭按钮
        const closeBtnHtml = CONFIG.display.pcFloat 
            ? `<button class="zf-close-btn" id="zf-close-btn">×</button>` 
            : '';

        formBox.innerHTML = `
            <div class="zf-top-bar">${langSwitchHtml}${closeBtnHtml}</div>
            <input class="zf-input" id="zf-nick" placeholder="${lang.nicknamePlaceholder}">
            ${projectHtml}
            <input class="zf-input" id="zf-contact" placeholder="${lang.contactPlaceholder}">
            <textarea class="zf-input zf-textarea" id="zf-msg" placeholder="${lang.messagePlaceholder}"></textarea>
            <button class="zf-submit-btn" id="zf-submit">${lang.submitBtn}</button>
            <div class="zf-tips" id="zf-tips"></div>
            ${qrcodeHtml}
            ${wechatRowHtml}
        `;

        // PC端折叠按钮
        if(CONFIG.display.pcFloat) {
            renderPCCollapseBtn();
            if(isCollapsed) {
                formBox.style.display = 'none';
                collapseBtn.style.display = 'flex';
            }
        }
    }

    // 渲染PC端折叠按钮
    function renderPCCollapseBtn() {
        collapseBtn = document.createElement('button');
        collapseBtn.className = 'zf-collapse-btn';
        collapseBtn.innerText = I18N[currentLang].expandBtn;
        collapseBtn.style.display = 'none';
        document.body.appendChild(collapseBtn);
        collapseBtn.onclick = () => {
            isCollapsed = false;
            if(CONFIG.display.pcRememberCollapse) localStorage.removeItem('pcFormCollapsed');
            collapseBtn.style.display = 'none';
            formBox.style.display = 'block';
        };
    }

    // 绑定事件
    function bindEvents() {
        const lang = I18N[currentLang];
        const isMobile = window.innerWidth <= 768;
        const mobileMode = CONFIG.display.mobileMode;

        // 输入框焦点状态
        document.querySelectorAll('.zf-input').forEach(i => {
            i.onfocus = () => isInputFocus = true;
            i.onblur = () => setTimeout(() => isInputFocus = false, 300);
        });

        // 提交按钮
        const submitBtn = document.getElementById('zf-submit');
        if(submitBtn) submitBtn.onclick = submitForm;

        // 关闭按钮
        const closeBtn = document.getElementById('zf-close-btn');
        if(closeBtn) {
            closeBtn.onclick = () => {
                if(isMobile && mobileMode === 'full') {
                    isMobileCollapsed = true;
                } else {
                    isCollapsed = true;
                    if(CONFIG.display.pcRememberCollapse) localStorage.setItem('pcFormCollapsed', 'true');
                }
                renderForm();
            };
        }

        // 语言切换按钮
        const langSwitchBtn = document.getElementById('zf-lang-switch');
        if(langSwitchBtn && !isMobile) {
            langSwitchBtn.onclick = () => {
                if(currentLang === 'zh') currentLang = 'zh-tw';
                else if(currentLang === 'zh-tw') currentLang = 'en';
                else currentLang = 'zh';
                renderForm();
            };
        }
    }

    // 表单提交逻辑
    async function submitForm() {
        if(submitting) return;
        const lang = I18N[currentLang];
        const isMobile = window.innerWidth <= 768;
        const mobileMode = CONFIG.display.mobileMode;

        let nickname = '', contact = '', message = '', project = I18N.zh.project.label[0];
        const submitBtn = document.getElementById('zf-submit');

        // 收集表单数据
        if(isMobile && mobileMode === 'mini') {
            contact = document.getElementById('zf-contact').value.trim();
            if(!contact) return showToast(lang.tips.contactEmpty);
        } else {
            nickname = document.getElementById('zf-nick').value.trim();
            contact = document.getElementById('zf-contact').value.trim();
            message = document.getElementById('zf-msg').value.trim();
            project = document.querySelector('input[name="zf-project"]:checked').value;
            
            if(!nickname) return showToast(lang.tips.nicknameEmpty);
            if(!contact) return showToast(lang.tips.contactEmpty);
        }

        // 提交状态锁定
        submitting = true;
        if(submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = lang.submitting;
        }

        // 构造提交数据
        const formData = new FormData();
        formData.append('nickname', safeText(nickname || 'mini用户'));
        formData.append('project', project);
        formData.append('contact', safeText(contact)); // 替换原phone字段为contact
        formData.append('message', safeText(message));
        formData.append('site_domain', window.location.hostname);
        formData.append('site_url', window.location.href);

        try {
            // 提交请求
            const res = await fetch(CONFIG.api, {
                method: 'POST',
                body: formData,
                cache: 'no-cache'
            });
            const data = await res.json();
            showToast(data.code === 1 ? lang.tips.success : data.msg);

            // 提交成功清空表单
            if(data.code === 1) {
                document.getElementById('zf-contact').value = '';
                if(!isMobile || mobileMode === 'full') {
                    document.getElementById('zf-nick').value = '';
                    document.getElementById('zf-msg').value = '';
                }
            }
        } catch (e) {
            showToast(lang.tips.networkError);
        } finally {
            // 解锁提交状态
            setTimeout(() => {
                submitting = false;
                if(submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = lang.submitBtn;
                }
            }, 1500);
        }
    }

    // 初始化
    function init() {
        // 合并配置
        CONFIG = mergeConfig(ZF_FORM_GLOBAL_CONFIG, window.ZF_FORM_CONFIG || {});
        // 设置默认语言
        currentLang = ['zh', 'zh-tw', 'en'].includes(CONFIG.language.default) 
            ? CONFIG.language.default 
            : 'zh';
        // 读取PC端折叠记忆状态
        isCollapsed = CONFIG.display.pcRememberCollapse && localStorage.getItem('pcFormCollapsed') === 'true';
        
        // 挂载全局方法 + 注入样式 + 渲染表单
        window.zfCopyWechat = copyWechat;
        injectStyles();
        renderForm();

        // 窗口大小变化重新渲染
        window.onresize = () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(renderForm, 500);
        };
    }

    // 页面加载完成初始化
    if(document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();