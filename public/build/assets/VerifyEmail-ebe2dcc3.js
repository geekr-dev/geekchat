import{v as m,h as f,o as a,c as g,w as s,a as o,u as e,X as h,f as p,g as _,b as i,d as r,n as y,l as v,e as b}from"./app-ef8b40ee.js";import{P as x,G as k}from"./PrimaryButton-49d2e909.js";import{_ as V}from"./_plugin-vue_export-helper-c27b6911.js";import"./ApplicationLogo-ed47ee4d.js";const w=i("div",{class:"mb-4 text-sm text-gray-600"}," Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another. ",-1),B={key:0,class:"mb-4 font-medium text-sm text-green-600"},E=["onSubmit"],N={class:"mt-4 flex items-center justify-between"},S={__name:"VerifyEmail",props:{status:String},setup(n){const c=n,t=m({}),u=()=>{t.post(route("verification.send"))},l=f(()=>c.status==="verification-link-sent");return(d,j)=>(a(),g(k,null,{default:s(()=>[o(e(h),{title:"Email Verification"}),w,e(l)?(a(),p("div",B," A new verification link has been sent to the email address you provided during registration. ")):_("v-if",!0),i("form",{onSubmit:b(u,["prevent"])},[i("div",N,[o(x,{class:y({"opacity-25":e(t).processing}),disabled:e(t).processing},{default:s(()=>[r(" Resend Verification Email ")]),_:1},8,["class","disabled"]),o(e(v),{href:d.route("logout"),method:"post",as:"button",class:"underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"},{default:s(()=>[r("Log Out")]),_:1},8,["href"])])],40,E)]),_:1}))}},G=V(S,[["__file","/home/geekr/Development/github/freechat/resources/js/Pages/Auth/VerifyEmail.vue"]]);export{G as default};
