"use client";

/**
 * AI Bijan — Command Center (BAB 24.2 keutamaan UI #1)
 * Fasa 1 papan "sekali pandang" gaya RekaDesk, ikut BAB 24.1 Design System.
 * Stack: Next.js 15 + TypeScript + Tailwind CSS (BAB 2, baris 244 blueprint).
 *
 * Drop-in: letak dalam app/(dashboard)/page.tsx atau components/CommandCenter.tsx.
 * Tema cerah/gelap guna strategi Tailwind darkMode:"class" — lihat tailwind-tokens.md.
 * Data di bawah = CONTOH. Ganti dengan data sebenar (lihat nota di hujung fail).
 */

import { useEffect, useState, type ReactNode } from "react";

// ---- Jenis data (kontrak untuk sambung ke API sebenar) ----
type Piutang = { nama: string; rm: number; nota: string; lewat: boolean };
type Tugas = { teks: string; jenis: string; siap: boolean };
type CommandCenterData = {
  salam: string;
  tarikh: string;
  live?: { tajuk: string; nota: string; peratus: number } | null;
  kutipan: { jumlah: number; ringkas: string; senarai: Piutang[] };
  seterusnya: { jam: string; ap: string; dalam: string; lencana: string; tajuk: string; lokasi: string; lagi?: string };
  minggu: { peratus: number; siap: number; jumlah: number; tugas: Tugas[] };
};

const rm = (n: number) => "RM " + n.toLocaleString("en-MY");

// ---- Komponen ----
export default function CommandCenter({ data }: { data: CommandCenterData }) {
  const [tema, setTema] = useState<"cerah" | "gelap">("cerah");

  useEffect(() => {
    const t = (localStorage.getItem("aib-tema") as "cerah" | "gelap") || "cerah";
    setTema(t);
  }, []);
  useEffect(() => {
    document.documentElement.classList.toggle("dark", tema === "gelap");
    localStorage.setItem("aib-tema", tema);
  }, [tema]);

  const d = data;

  return (
    <div className="min-h-screen bg-[#f6f3ec] dark:bg-[#15110c] text-[#1b1510] dark:text-[#f4ecdf] pb-24 lg:pb-10">
      <div className="mx-auto max-w-[460px] lg:max-w-[900px] px-4 lg:px-6 pt-5">
        {/* Header + suis tema */}
        <header className="flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-[#e8a33d] to-[#c8763e] font-extrabold text-[#241803] shadow-lg shadow-[#c8763e]/30">
              b
            </div>
            <div>
              <b className="block text-[17px] font-extrabold leading-tight">AI Bijan</b>
              <span className="text-[11px] text-[#7a7064] dark:text-[#a89a86]">Boardroom · Kedai Bunga Bijan</span>
            </div>
          </div>
          <div className="flex gap-1 rounded-full border border-[#ebe4d7] dark:border-[#352a20] bg-white dark:bg-[#201913] p-[3px]">
            {(["cerah", "gelap"] as const).map((t) => (
              <button
                key={t}
                onClick={() => setTema(t)}
                className={`rounded-full px-3 py-1.5 text-xs font-semibold transition ${
                  tema === t
                    ? "bg-[#241b16] text-[#fdf7ec] dark:bg-[#f0ad48] dark:text-[#241803]"
                    : "text-[#7a7064] dark:text-[#a89a86]"
                }`}
              >
                {t === "cerah" ? "☀ Cerah" : "☾ Gelap"}
              </button>
            ))}
          </div>
        </header>

        {/* Salam */}
        <div className="mb-4 mt-3.5 px-0.5">
          <h1 className="text-[23px] font-extrabold tracking-tight">{d.salam}</h1>
          <p className="mt-0.5 text-[13.5px] text-[#7a7064] dark:text-[#a89a86]">{d.tarikh}</p>
        </div>

        {/* Jalur Langsung (gaya Live Activity dalam app) */}
        {d.live && (
          <div className="mb-3.5 flex items-center gap-3 rounded-2xl border border-[#ebe4d7] dark:border-[#352a20] bg-white dark:bg-[#201913] p-3 shadow-sm">
            <span className="grid h-[34px] w-[34px] flex-none place-items-center rounded-[10px] bg-[#e8a33d]/15">
              <span className="h-2.5 w-2.5 animate-pulse rounded-full bg-[#e8a33d]" />
            </span>
            <div className="min-w-0 flex-1">
              <b className="block text-[13px] font-bold">{d.live.tajuk}</b>
              <small className="text-[11.5px] text-[#7a7064] dark:text-[#a89a86]">{d.live.nota}</small>
              <div className="mt-1.5 h-[5px] overflow-hidden rounded-full bg-[#ebe4d7] dark:bg-[#352a20]">
                <i className="block h-full rounded-full bg-gradient-to-r from-[#e8a33d] to-[#c8763e]" style={{ width: `${d.live.peratus}%` }} />
              </div>
            </div>
            <span className="whitespace-nowrap rounded-full bg-[#e8a33d]/15 px-2 py-[3px] text-[10.5px] font-bold text-[#e8a33d]">LANGSUNG</span>
          </div>
        )}

        <div className="lg:grid lg:grid-cols-[1.05fr_.95fr] lg:items-start lg:gap-4">
          {/* KAD 1: Duit belum kutip */}
          <section className="mb-3.5 rounded-[22px] border border-[#ebe4d7] dark:border-[#352a20] bg-white dark:bg-[#201913] p-[18px] shadow-sm lg:row-span-2 lg:mb-0">
            <div className="flex items-center justify-between">
              <Tajuk>Duit belum kutip</Tajuk>
              <div className="font-jakarta text-[20px] font-extrabold">{rm(d.kutipan.jumlah)}</div>
            </div>
            <div className="text-[12.5px] font-semibold text-[#c4502e] dark:text-[#e8795a]">{d.kutipan.ringkas}</div>
            {d.kutipan.senarai.map((p, i) => (
              <div key={i} className="flex items-center gap-3 border-t border-[#ebe4d7] dark:border-[#352a20] py-3 first:mt-2.5">
                <div className="min-w-0 flex-1">
                  <b className="block truncate text-[14.5px] font-semibold">{p.nama}</b>
                  <small className={`text-[11.5px] font-semibold ${p.lewat ? "text-[#c4502e] dark:text-[#e8795a]" : "font-medium text-[#7a7064] dark:text-[#a89a86]"}`}>{p.nota}</small>
                </div>
                <div className="font-jakarta text-[15px] font-bold">{rm(p.rm)}</div>
                <button className="flex items-center gap-1.5 whitespace-nowrap rounded-full bg-[#e8a33d] px-3.5 py-2 text-[12.5px] font-bold text-[#2a1d08]">
                  <IconChat /> Ingatkan
                </button>
              </div>
            ))}
          </section>

          {/* KAD 2: Seterusnya */}
          <section className="mb-3.5 rounded-[22px] border border-[#ebe4d7] dark:border-[#352a20] bg-white dark:bg-[#201913] p-[18px] shadow-sm lg:mb-0">
            <Tajuk>Seterusnya</Tajuk>
            <div className="mt-1 flex items-start gap-4">
              <div className="flex-none">
                <div className="font-jakarta text-[30px] font-extrabold leading-none">
                  {d.seterusnya.jam}<span className="ml-0.5 text-[13px] font-bold text-[#7a7064] dark:text-[#a89a86]">{d.seterusnya.ap}</span>
                </div>
                <div className="mt-1.5 text-[12.5px] font-bold text-[#e8a33d]">{d.seterusnya.dalam}</div>
              </div>
              <div className="flex-1 border-l border-[#ebe4d7] dark:border-[#352a20] pl-4">
                <span className="mb-1.5 inline-block rounded-md bg-[#e8a33d]/20 px-2 py-[3px] text-[10px] font-extrabold tracking-wide text-[#5a3f12] dark:text-[#f0ad48]">{d.seterusnya.lencana}</span>
                <b className="block text-[16px] font-bold">{d.seterusnya.tajuk}</b>
                <div className="mt-0.5 text-[12.5px] text-[#7a7064] dark:text-[#a89a86]">📍 {d.seterusnya.lokasi}</div>
                {d.seterusnya.lagi && <div className="mt-2.5 border-t border-[#ebe4d7] dark:border-[#352a20] pt-2.5 text-[12px] text-[#7a7064] dark:text-[#a89a86]">{d.seterusnya.lagi}</div>}
              </div>
            </div>
          </section>

          {/* KAD 3: Tugas minggu ni */}
          <section className="mb-3.5 rounded-[22px] border border-[#ebe4d7] dark:border-[#352a20] bg-white dark:bg-[#201913] p-[18px] shadow-sm lg:mb-0">
            <div className="flex items-center justify-between">
              <Tajuk>Tugas minggu ni</Tajuk>
              <div className="text-xs font-semibold text-[#7a7064] dark:text-[#a89a86]">{d.minggu.siap} dari {d.minggu.jumlah} siap</div>
            </div>
            <div className="my-3 flex items-baseline justify-between">
              <div className="font-jakarta text-[26px] font-extrabold">{d.minggu.peratus}%<span className="text-[18px] text-[#7a7064] dark:text-[#a89a86]"> siap</span></div>
              <div className="text-xs font-semibold text-[#7a7064] dark:text-[#a89a86]">{d.minggu.jumlah - d.minggu.siap} belum selesai</div>
            </div>
            <div className="mb-3.5 h-[9px] overflow-hidden rounded-full bg-[#ebe4d7] dark:bg-[#352a20]">
              <i className="block h-full rounded-full bg-gradient-to-r from-[#e8a33d] to-[#c8763e]" style={{ width: `${d.minggu.peratus}%` }} />
            </div>
            {d.minggu.tugas.map((t, i) => (
              <div key={i} className="flex items-center gap-3 py-2">
                <span className={`h-5 w-5 flex-none rounded-[7px] border-2 ${t.siap ? "border-[#e8a33d] bg-[#e8a33d]" : "border-[#ebe4d7] dark:border-[#352a20]"}`}>
                  {t.siap && <svg viewBox="0 0 24 24" className="h-full w-full p-0.5 text-[#2a1d08]"><path fill="none" stroke="currentColor" strokeWidth="3" d="M5 12l5 5 9-10" /></svg>}
                </span>
                <div className={`flex-1 text-[14px] ${t.siap ? "text-[#7a7064] line-through dark:text-[#a89a86]" : "font-medium"}`}>{t.teks}</div>
                <div className="text-[11px] font-bold text-[#7a7064] dark:text-[#a89a86]">{t.jenis}</div>
              </div>
            ))}
          </section>
        </div>
      </div>

      {/* Bar navigasi bawah (HP sahaja) */}
      <nav className="fixed inset-x-0 bottom-0 z-10 flex justify-around border-t border-[#ebe4d7] bg-white/90 px-1.5 py-2 backdrop-blur dark:border-[#352a20] dark:bg-[#201913]/90 lg:hidden">
        {[["Utama", true], ["Tempahan", false], ["Kutipan", false], ["Boardroom", false], ["Lagi", false]].map(([l, aktif]) => (
          <span key={l as string} className={`px-2.5 py-1 text-[10.5px] font-semibold ${aktif ? "text-[#1b1510] dark:text-[#f4ecdf]" : "text-[#7a7064] dark:text-[#a89a86]"}`}>{l}</span>
        ))}
      </nav>
    </div>
  );
}

function Tajuk({ children }: { children: ReactNode }) {
  return (
    <div className="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-[#7a7064] dark:text-[#a89a86]">
      <span className="h-[7px] w-[7px] rounded-sm bg-[#e8a33d]" />
      {children}
    </div>
  );
}
function IconChat() {
  return <svg viewBox="0 0 24 24" className="h-3.5 w-3.5" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" /></svg>;
}

/**
 * ——— Sambung data sebenar (ganti CONTOH) ———
 * kutipan   ← B-Billing: invois status belum_bayar / lewat_tempoh (jumlah, nama pelanggan, baki, hari lewat).
 *             butang Ingatkan → POST draf WhatsApp; HANTAR ikut pagar (owner lulus dulu).
 * seterusnya← B-Booking / kalendar: tempahan/tugas akan datang terdekat + kiraan masa.
 * minggu    ← senarai tugas minggu semasa (Brain Hub / modul tugas) → kira % siap.
 * live      ← kempen B-Marketing yang sedang berjalan (null kalau tiada).
 *
 * Contoh pemanggilan (server component tarik data, hantar ke sini):
 *   <CommandCenter data={await getCommandCenter(tenantId)} />
 */
