# AI BIJAN SaaS --- Malaysia Industry Coverage Master Blueprint

**Versi:** 4.0\
**Tarikh semakan:** 18 Oktober 2026\
**Skop:** Direktori sektor dan jenis perniagaan Malaysia, pemetaan
modul, keperluan khusus industri dan strategi pembangunan\
**Status:** Dokumen induk perancangan; bukan pengesahan bahawa semua
modul telah dibangunkan atau mendapat kelulusan kawal selia.

------------------------------------------------------------------------

## 1. Tujuan

Dokumen ini memperluas AI BIJAN SaaS daripada senarai modul umum kepada
ekosistem yang direka untuk menyokong pelbagai jenis perniagaan di
Malaysia --- termasuk perniagaan mikro, bekerja sendiri, perusahaan
kecil dan sederhana, organisasi berbilang cawangan serta perusahaan
lebih besar.

Contoh khusus seperti **pengusaha van sekolah** mesti dikenal pasti
secara nyata. Ia bukan sekadar "logistik" generik: pengusaha van sekolah
memerlukan pengurusan murid, penjaga, laluan, jadual sekolah, bayaran
bulanan, kehadiran/serahan murid, kenderaan, pemandu, kecemasan dan
rekod kebenaran.

### Prinsip utama

1.  **Satu Shared Core, banyak modul khusus.**
2.  **Industri bukan sekadar label atau tema dashboard.** Setiap
    industri perlu mempunyai proses kerja, data, role, laporan dan ujian
    penerimaan sendiri.
3.  Pelanggan hanya mengaktifkan modul yang diperlukan.
4.  Satu organisasi boleh mempunyai beberapa aktiviti perniagaan,
    cawangan atau jenama.
5.  Gunakan konfigurasi industri untuk menyesuaikan sistem tanpa membina
    semula keseluruhan aplikasi.
6.  Jangan dakwa sesuatu industri "disokong sepenuhnya" sehingga
    workflow pentingnya dibangunkan, diuji dan didokumenkan.
7.  Keperluan lesen, permit, cukai, keselamatan, data peribadi dan
    pematuhan mesti disahkan mengikut industri dan lokasi.

------------------------------------------------------------------------

## 2. Rujukan Klasifikasi Industri Malaysia

Gunakan **Malaysia Standard Industrial Classification (MSIC)** DOSM
sebagai taksonomi asas untuk menamakan, menyusun dan memetakan aktiviti
perniagaan. MSIC ialah klasifikasi aktiviti ekonomi, bukannya senarai
lengkap semua ciri perisian yang diperlukan oleh setiap jenis
perniagaan.

Sumber rasmi: - Portal carian Kod MSIC 2025, DOSM:
https://economiccensus.dosm.gov.my/portal-main/msic-2025 - OpenDOSM ---
katalog MSIC: https://open.dosm.gov.my/data-catalogue/msic - DOSM ---
Economic Census, All Sectors:
https://www.dosm.gov.my/portal-main/release-content/economic-census--all-sectors -
DOSM --- MSME Performance:
https://www.dosm.gov.my/portal-main/release-content/micro-small--medium-enterprises-msmes-performance-2025

MSIC dikemas kini mengikut versi. Oleh itu: - Simpan kod MSIC, label,
versi klasifikasi dan tarikh semakan dalam direktori industri. - Semak
kod aktiviti sebenar melalui portal rasmi sebelum menggunakannya dalam
pendaftaran, laporan rasmi atau integrasi kerajaan. - Jangan mereka atau
hardcode kod MSIC untuk contoh perniagaan tanpa pengesahan. - Satu
perniagaan mungkin menjalankan lebih daripada satu aktiviti; benarkan
aktiviti utama dan aktiviti sekunder.

------------------------------------------------------------------------

## 3. Seni Bina Produk AI BIJAN

### 3.1 Shared Core

Komponen yang digunakan merentas industri: - B-Auth & Security -
Tenant/Organization, Membership, Role & Permission - Subscription,
Module Entitlement & Billing - Usage Metering dan AI Token Wallet -
Notifications - File & Document Storage - Audit Trail - API foundation,
Webhooks dan Integrations - Localization, currency, timezone dan tax
configuration - Import/export data, backup dan recovery

### 3.2 Business Modules sedia dirancang

-   B-Virtual Office
-   B-HRMS
-   B-Sign & Docs
-   B-Projects
-   B-Assets
-   B-POS System
-   B-Site
-   B-Link
-   B-Marketplace
-   B-Affiliate
-   B-Loyalty
-   B-Omnichannel
-   B-Booking
-   B-Marketing
-   B-Helpdesk / B-Desk
-   B-COGS Engine
-   B-Inventory & Warehouse
-   B-BOM & Manufacturing
-   B-Finance
-   B-Invoicing
-   B-Procurement
-   B-Logistics
-   B-Connect / API Gateway
-   B-Analytics
-   B-Brain Hub

### 3.3 Industry Modules yang perlu dibangunkan mengikut keutamaan

-   B-School Transport
-   B-Transport & Fleet
-   B-Childcare & Education
-   B-Hospitality
-   B-Clinic & Care
-   B-Construction
-   B-Property
-   B-Automotive
-   B-Agriculture
-   B-Event & Rental
-   B-Quality & Compliance
-   B-Subscription Business
-   B-Professional Practice
-   B-Travel & Tour
-   B-Waste & Recycling
-   B-Security Services
-   B-Community & Association
-   B-Pet Services
-   B-Repair & Field Service
-   B-Finance Services (hanya untuk aktiviti yang dibenarkan dan sesuai)

------------------------------------------------------------------------

## 4. Direktori Industri Malaysia dan Pemetaan Modul

Senarai berikut ialah direktori produk yang luas untuk perancangan. Ia
bukan senarai rasmi kod MSIC satu demi satu dan bukan dakwaan bahawa
setiap subjenis mempunyai modul siap. Setiap item perlu dipetakan kepada
kod MSIC rasmi yang tepat semasa pelaksanaan.

**Petunjuk modul umum** - **POS** = B-POS - **INV** = B-Inventory &
Warehouse - **FIN** = B-Finance - **BILL** = B-Invoicing - **BOOK** =
B-Booking - **HR** = B-HRMS - **PROJ** = B-Projects - **ASSET** =
B-Assets - **MKT** = B-Marketing - **CRM/OMNI** = B-Omnichannel -
**LOG** = B-Logistics - **BRAIN** = B-Brain Hub - **SITE** = B-Site -
**DOCS** = B-Sign & Docs - **HELP** = B-Helpdesk - **PROC** =
B-Procurement - **LOYALTY** = B-Loyalty - **ANALYTICS** = B-Analytics

### 4.1 Pengangkutan, mobiliti dan operasi kenderaan

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus yang      Modul BIJAN
                          diperlukan              
  ----------------------- ----------------------- -----------------------
  **Pengusaha van         Murid, penjaga,         **B-School
  sekolah**               sekolah, laluan dan     Transport** + BILL +
                          hentian, jadual         FIN + LOG/Fleet +
                          pickup/drop-off,        CRM/OMNI + BRAIN
                          langganan bulanan,      
                          status bayaran,         
                          kehadiran/serahan       
                          murid, senarai          
                          kecemasan, notifikasi   
                          kelewatan, kenderaan,   
                          pemandu dan rekod       
                          dokumen                 

  Bas sekolah             Kapasiti, route,        B-School Transport +
                          senarai penumpang,      Fleet + HR + BILL
                          jadual, pemandu,        
                          penyelenggaraan,        
                          kontrak sekolah dan     
                          insiden                 

  Van pekerja / shuttle   Kontrak majikan,        B-Transport & Fleet +
                          senarai penumpang,      HR + BILL + FIN
                          shift, pickup point,    
                          route dan invois        
                          berkala                 

  Bas ekspres / bas       Tempahan tempat duduk,  B-Transport & Fleet +
  persiaran               jadual perjalanan,      BOOK + BILL + FIN
                          tiket, manifest,        
                          operator, trip cost dan 
                          pembatalan              

  Teksi / kereta sewa     Tempahan, pemandu,      B-Transport & Fleet +
  berlesen / chauffeur    perjalanan, kadar,      BOOK + BILL
                          invois, jadual          
                          kenderaan dan rekod     
                          perjalanan              

  E-hailing / pemandu     Trip assignment,        B-Transport & Fleet
  persendirian            earnings, service area  
                          dan rekod perjalanan;   
                          tertakluk kepada model  
                          dan peraturan operasi   

  Lori / pengangkutan     Job dispatch,           LOG + Fleet + FIN
  barang                  consignment, muatan,    
                          proof of delivery,      
                          pelanggan kontrak dan   
                          kos trip                

  Kurier / last-mile      Parcel, scan event,     LOG + Fleet + B-Connect
  delivery                route, delivery         
                          attempt, proof of       
                          delivery dan returns    

  Syarikat pindah rumah   Survey, quotation, kru, BOOK + LOG + PROJ +
                          lori, inventori barang, BILL
                          jadual dan tuntutan     
                          kerosakan               

  Kereta sewa / motosikal Availability, booking,  BOOK + ASSET + FIN +
  sewa                    deposit, pemeriksaan    DOCS
                          sebelum/selepas,        
                          mileage dan servis      

  Pengusaha parking       Lokasi, bay, pass,      POS/BOOK + FIN +
                          season parking,         Analytics
                          entry/exit, kutipan dan 
                          reconciliation          

  Bengkel / pusat servis  Kenderaan, job card,    **B-Automotive** +
  automotif               inspection, alat ganti, INV + BOOK + BILL + FIN
                          labour, warranty dan    
                          service reminder        

  Tayar, bateri, aksesori SKU, pemasangan,        POS + INV + BOOK +
  dan detailing           booking, kerja, stok    LOYALTY
                          dan servis ulangan      

  Tow truck / roadside    Dispatch, lokasi, jenis B-Repair & Field
  assistance              bantuan, SLA, kenderaan Service + LOG + BILL
                          bantuan, quotation dan  
                          bukti kerja             

  Pengurusan fleet        Kenderaan, pemandu,     **B-Transport &
                          fuel, mileage, servis,  Fleet** + ASSET + HR +
                          permit, downtime dan    FIN
                          kos setiap kenderaan    
  -----------------------------------------------------------------------

**Keperluan penting untuk van sekolah:** sistem mesti menyokong rekod
penjaga yang sah, kontak kecemasan, individu dibenarkan mengambil murid,
status pickup/drop-off dan audit perubahan. Data kanak-kanak perlu akses
terhad. Tracking lokasi langsung hanya jika benar-benar diperlukan,
mendapat kebenaran yang sesuai dan dilaksanakan dengan perlindungan
data.

### 4.2 Pendidikan, kanak-kanak dan penjagaan

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Taska / nursery         Profil kanak-kanak dan  **B-Childcare &
                          penjaga, kehadiran,     Education** + BILL +
                          yuran, jadual, kontak   CRM/OMNI
                          kecemasan, rekod        
                          serahan dan komunikasi  

  Tadika                  Murid, kelas, guru,     B-Childcare &
                          kehadiran, yuran,       Education + HR + BILL
                          aktiviti, laporan dan   
                          portal penjaga          

  Pusat transit selepas   Jadual pickup, murid,   B-Childcare &
  sekolah                 penjaga, kehadiran,     Education + B-School
                          makanan dan serahan     Transport

  Pusat tuisyen           Pelajar, subjek, kelas, B-Childcare &
                          jadual, guru,           Education + BOOK + HR +
                          kehadiran, yuran dan    BILL
                          keputusan               

  Sekolah swasta / pusat  Enrolment, kelas, guru, B-Childcare &
  latihan                 kehadiran, yuran,       Education + FIN + HR
                          peperiksaan, sijil dan  
                          laporan                 

  Tutor persendirian      Jadual, pelajar, pakej  BOOK + BILL + SITE
                          sesi, invois dan        
                          kemajuan                

  Pusat kemahiran /       Kursus, peserta,        B-Childcare &
  latihan korporat        trainer, jadual,        Education + PROJ + BILL
                          penilaian, sijil dan    
                          kontrak                 

  Penjagaan warga emas /  Jadual penjaga,         B-Clinic & Care +
  penjagaan di rumah      lawatan, tugasan,       BOOK + HR
                          kontak keluarga dan     
                          rekod perkhidmatan yang 
                          dibenarkan              

  Pengasuh / babysitter   Profil penjaga, jadual, B-Booking + B-Childcare
  agency                  padanan keluarga,       & Education + DOCS
                          booking, dokumen dan    
                          feedback                
  -----------------------------------------------------------------------

### 4.3 Retail, perdagangan dan jualan

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Kedai runcit /          POS, barcode, stok,     POS + INV + PROC + FIN
  minimarket              supplier, harga dan     
                          multi-outlet            

  Pasar raya / kedai      SKU besar, promosi,     POS + INV + PROC + MKT
  serbaneka               multi-cawangan,         
                          purchase planning dan   
                          stock count             

  Butik / fesyen          Saiz, warna, variasi,   POS + INV + LOYALTY
                          collection, return dan  
                          loyalty                 

  Kedai telefon /         Serial/IMEI, warranty,  POS + INV +
  komputer                repair intake,          B-Automotive-style
                          accessories dan         repair workflow
                          trade-in jika           
                          dibangunkan             

  Farmasi / produk        Batch, expiry,          POS + INV + PROC;
  kesihatan               supplier, product       kawalan khusus
                          restrictions dan rekod  diperlukan
                          jualan yang sesuai      

  Kedai perkakasan /      Unit ukuran, harga      POS + INV + BILL + LOG
  bahan binaan            projek, quotation,      
                          delivery dan akaun      
                          pelanggan               

  Kedai perabot           Produk besar,           POS + INV + LOG + BOOK
                          konfigurasi, deposit,   
                          penghantaran dan        
                          pemasangan              

  Pemborong / pengedar    Harga tier, customer    INV + BILL + LOG + FIN
                          credit, sales order,    
                          warehouse, delivery dan 
                          sales rep               

  Pengimport /            Purchase/sales orders,  PROC + LOG + FIN + DOCS
  pengeksport             shipment, dokumen,      
                          landed cost dan         
                          currency                

  Penjual online /        Product listing, order, B-Marketplace + INV +
  marketplace seller      stock sync, fulfilment, LOG + FIN
                          returns dan platform    
                          fee                     

  Dropship / reseller     Supplier catalogue,     B-Marketplace +
                          order routing,          B-Affiliate + LOG
                          commission dan status   
                          fulfilment              

  Penjual produk digital  Digital listing,        B-Marketplace + BILL +
                          payment, access         B-Connect
                          entitlement,            
                          download/delivery dan   
                          refund                  

  Mesin layan diri        Produk, restock route,  POS + INV + Fleet/LOG
                          cashless sales, machine 
                          status dan stock        

  Kedai pajak / aktiviti  Inventory/collateral    Perlu discovery dan
  kewangan terkawal       records atau workflow   legal review khusus
                          yang dibenarkan sahaja  
  -----------------------------------------------------------------------

### 4.4 Makanan, minuman, pertanian dan perikanan

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Restoran / kafe         Menu, meja, kitchen     POS + INV + COGS + HR
                          order, resipi, wastage, 
                          shift dan delivery      

  Gerai / food truck      Menu ringkas, POS mudah POS + INV + COGS
                          alih, lokasi, stok,     
                          shift dan jualan harian 

  Katering                Event order, menu,      B-Event & Rental +
                          headcount, costing,     COGS + INV + BILL
                          prep plan, delivery dan 
                          deposit                 

  Bakeri / kek tempahan   Resipi, batch, expiry,  POS + BOM + COGS + BOOK
                          pre-order, custom cake  
                          dan delivery            

  Dapur pusat / cloud     Multi-brand menu,       POS + BOM + INV + COGS
  kitchen                 kitchen capacity,       
                          ingredient planning dan 
                          order routing           

  Pengusaha makanan sejuk Batch, expiry,          INV + LOG + COGS + PROC
  beku                    cold-chain records jika 
                          tersedia, distributor   
                          dan returns             

  Pengeluar makanan /     BOM, batch, recipe      BOM + INV + COGS +
  minuman                 version, QA, expiry,    Quality
                          recall dan production   
                          costing                 

  Petani / pekebun        Plot, tanaman, musim,   B-Agriculture + INV +
                          input, aktiviti ladang, COGS
                          hasil dan kos           

  Penternak ayam / lembu  Batch ternakan,         B-Agriculture + INV +
  / kambing               makanan, kesihatan,     FIN
                          pembiakan, mortaliti    
                          dan jualan              

  Akuakultur / perikanan  Kolam, batch, feed,     B-Agriculture + INV +
                          mortality, harvest,     LOG
                          grading dan cold chain  

  Nelayan / pengusaha bot Trip, tangkapan, bahan  B-Agriculture/Marine
                          api, kru, jualan dan    workflow + Fleet + FIN
                          rekod bot               

  Nurseri / landskap      Tanaman, stok hidup,    INV + PROJ + BOOK
                          projek landskap,        
                          pekerja dan jadual      
                          penyelenggaraan         

  Pembekal baja / benih   SKU, batch, expiry,     POS + INV + PROC + LOG
                          dealer price dan        
                          delivery                
  -----------------------------------------------------------------------

### 4.5 Servis rumah, servis lapangan dan penjagaan diri

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Barber / salon / spa    Stylist, slot, tempoh   BOOK + POS + HR +
                          servis, komisen, produk LOYALTY
                          dan loyalty             

  Urut / refleksologi /   Therapist, bilik,       BOOK + BILL + CRM
  kecantikan              pakej, booking, consent 
                          jika relevan dan        
                          follow-up               

  Pembersihan rumah /     Lokasi, recurring job,  B-Repair & Field
  pejabat                 crew, checklist, masa   Service + HR + BOOK
                          kerja dan bukti selesai 

  Pest control            Site, jadual rawatan,   B-Repair & Field
                          bahan, laporan lawatan  Service + INV + DOCS
                          dan peringatan          

  Plumbing / elektrik /   Job request,            B-Repair & Field
  aircond                 technician, spare       Service + INV + BOOK
                          parts, quotation, work  
                          order dan warranty      

  Renovation / handyman   Site visit, quotation,  PROJ + BOOK + INV +
                          material, milestone,    BILL
                          gambar sebelum/selepas  
                          dan invoice             

  Laundry / dobi          Order intake, tag       POS + INV/Order
                          pakaian, service        workflow + LOG +
                          status, pickup/delivery LOYALTY
                          dan membership          

  Tailor / alteration     Measurement, job        BOOK + POS + INV
                          ticket, fitting date,   
                          material dan baki       
                          bayaran                 

  Locksmith / appliance   Service ticket,         B-Repair & Field
  repair                  model/serial,           Service + INV + BILL
                          diagnosis, parts,       
                          warranty dan visit      

  Pest/landscape          Contract recurring,     B-Repair & Field
  maintenance             route, checklist,       Service + ASSET + INV
                          chemicals/materials dan 
                          service report          

  Penjagaan haiwan /      Pet profile, owner,     B-Pet Services + BOOK +
  grooming                appointment,            BILL
                          vaccination notes yang  
                          sesuai dan service      
                          history                 

  Veterinar               Appointment dan rekod   B-Clinic & Care + B-Pet
                          rawatan haiwan mengikut Services
                          keperluan klinikal dan  
                          kawalan akses           
  -----------------------------------------------------------------------

### 4.6 Pembinaan, hartanah dan kemudahan

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Kontraktor utama /      Tender, BOQ, progress   B-Construction + PROJ +
  subkontraktor           claim, VO, site diary,  PROC + FIN
                          subcontractor dan       
                          cost-to-complete        

  Renovation / interior   Client brief, design    B-Construction + PROJ +
  design                  versions, quotation,    DOCS
                          materials, milestones   
                          dan handover            

  Arkitek / jurutera /    Project deliverables,   B-Professional
  juruukur                timesheet,              Practice + PROJ + DOCS
                          drawing/document        
                          versions, approval dan  
                          invois                  

  Pemaju hartanah         Unit inventory, booking B-Property + BILL +
                          fee, sales progress,    DOCS
                          purchaser records dan   
                          handover                

  Ejen hartanah           Listing, lead, viewing, B-Property + CRM/OMNI +
                          owner mandate,          DOCS
                          commission dan tenancy  
                          reminders               

  Pengurusan sewa         Unit, tenant, lease,    B-Property + FIN + BILL
                          deposit, recurring      
                          rent, arrears dan       
                          maintenance             

  Pengurusan kondominium  Unit, resident,         B-Property + HELP +
  / JMB / MC              complaint, visitor,     BOOK + FIN
                          facility booking, dues  
                          dan contractor          

  Facility management     Asset, preventive       ASSET + B-Repair &
                          maintenance, work       Field Service + HELP
                          order, vendor SLA dan   
                          inspection              

  Pengurusan dewan /      Calendar, capacity,     B-Event & Rental +
  ruang                   package, deposit,       BOOK + BILL
                          setup/cleanup dan       
                          booking                 

  Arkitek landskap /      Site, crew, material,   PROJ + B-Repair & Field
  penyelenggaraan taman   recurring schedule dan  Service
                          proof of work           
  -----------------------------------------------------------------------

### 4.7 Kesihatan, penjagaan dan perkhidmatan profesional

  --------------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus              Modul BIJAN
  ----------------------- -------------------------- -----------------------
  Klinik am / pergigian   Appointment, patient       B-Clinic & Care +
                          records, consent, billing  BOOK + BILL
                          dan rekod klinikal dalam   
                          skop yang sesuai           

  Fisioterapi / rehab     Session plan, appointment, B-Clinic & Care + BOOK
                          attendance, progress       
                          records dan package        
                          billing                    

  Makmal / diagnostik     Order, specimen tracking,  Modul khusus +
                          result workflow dan audit  B-Connect
                          jika skop dan integrasi    
                          disahkan                   

  Optometri               Appointment,               B-Clinic & Care + POS +
                          prescription/measurement   BOOK
                          records yang sesuai,       
                          product sales dan          
                          follow-up                  

  Farmasi komuniti        POS, stock, batch/expiry,  POS + INV + PROC;
                          procurement dan kawalan    peraturan produk perlu
                          produk                     disahkan

  Firma guaman            Matter/file, conflict      B-Professional
                          checks, deadlines, time    Practice + DOCS +
                          billing, documents dan     PROJ + BILL
                          trust-account workflow     
                          jika dibenarkan            

  Akauntan / tax agent    Client records,            B-Professional
                          engagement, deadlines,     Practice + DOCS + PROJ
                          document checklist,        
                          timesheet dan billing      

  Konsultan / agensi      Proposal, project,         PROJ + BILL + DOCS
                          retainer, timesheet,       
                          deliverable dan invoice    

  Jurugambar /            Booking, package, event    BOOK + B-Event &
  videografer             schedule, shot list, file  Rental + BILL
                          delivery dan deposit       

  Agensi kreatif /        Campaign, client approval, MKT + PROJ + DOCS
  pemasaran               creative assets, budget    
                          dan reporting              

  IT managed services     Ticket, SLA, asset/device  HELP + ASSET + B-Repair
                          register, technician,      & Field Service
                          recurring contract dan     
                          billing                    

  Penterjemah / penulis / Project, word/page scope,  PROJ + DOCS + BILL
  editor                  revision, deadline,        
                          approval dan invoice       

  Rekrutmen / HR          Candidate pipeline,        B-Professional
  consultancy             vacancy, interview, client Practice + HR
                          placement dan fee          workflow + CRM

  Juruaudit / pemeriksa   Engagement, evidence       DOCS + PROJ + Quality &
                          checklist, finding, review Compliance
                          dan report versions        
  --------------------------------------------------------------------------

### 4.8 Pembuatan, pembekalan dan industri

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Kilang am / OEM         BOM, work order,        BOM + INV + COGS +
                          capacity, QA, batch,    Quality
                          scrap dan unit cost     

  Tekstil / pakaian       Material, size/color    BOM + INV + COGS
                          variants, cutting,      
                          production stages dan   
                          QC                      

  Perabot / kerja kayu    Custom dimensions,      BOM + INV + PROJ + LOG
                          material consumption,   
                          production, delivery    
                          dan installation        

  Percetakan              Job estimate, artwork   PROJ + BOM/COGS + INV
                          approval, paper/ink     
                          usage, production       
                          stages dan delivery     

  Plastik / logam /       BOM, serial/batch,      BOM + ASSET + Quality +
  elektronik              machine maintenance, QC INV
                          dan traceability        

  Pembekal industri       SKU, quotation,         INV + PROC + BILL + LOG
                          contract price, PO,     
                          delivery dan            
                          receivables             

  Bengkel fabrikasi       Drawing/version, job    PROJ + BOM + INV
                          order, material,        
                          labour, QA dan          
                          installation            

  Kitar semula / scrap    Collection, material    INV + LOG + COGS
                          grade, weight,          
                          supplier, processing    
                          dan sales               

  Pengurusan sisa         Collection schedule,    B-Waste & Recycling +
                          route, bin, proof of    LOG + BILL
                          service dan billing     

  Sewa mesin/peralatan    Asset availability,     ASSET + B-Event &
                          contract, deposits,     Rental + BILL
                          usage, maintenance dan  
                          return inspection       
  -----------------------------------------------------------------------

### 4.9 Hospitaliti, pelancongan, acara dan hiburan

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Hotel / resort /        Room availability,      B-Hospitality + BOOK +
  homestay                rates, booking,         BILL
                          check-in/out,           
                          housekeeping dan        
                          deposits                

  Ejen pelancongan        Tour package, passenger B-Travel & Tour +
                          manifest, supplier,     BOOK + DOCS
                          itinerary, payment      
                          schedule dan            
                          cancellation            

  Tour guide              Availability, language, B-Travel & Tour + BOOK
                          tour assignment, group  
                          size dan payout         

  Operator aktiviti /     Slot, capacity, waiver, BOOK + B-Event & Rental
  rekreasi                equipment, guide dan    
                          weather cancellation    
                          policy                  

  Wedding planner         Client brief, vendor,   B-Event & Rental +
                          timeline, package,      PROJ + BILL
                          deposit dan milestone   

  Vendor perkahwinan      Package, date           B-Event & Rental + BOOK
                          availability,           
                          quotation, contract,    
                          crew dan deposit        

  Dewan / meeting room /  Resource calendar,      B-Event & Rental + BOOK
  coworking               hourly/daily rate,      
                          access, deposit dan     
                          recurring booking       

  Sewaan PA system /      Asset availability,     B-Event & Rental +
  kanopi / kerusi         delivery, setup, return ASSET + LOG
                          inspection dan damage   
                          deposit                 

  Penganjur konsert /     Event, ticket           B-Event & Rental +
  tiket                   inventory, check-in/QR, Marketplace + FIN
                          refund policy dan       
                          settlement              

  Gimnasium / studio      Membership, class       B-Subscription
  kecergasan              booking, trainer,       Business + BOOK + BILL
                          attendance, recurring   
                          payment dan freeze      
                          membership              

  Pusat hiburan /         POS, booking, time      POS + BOOK + ASSET
  permainan               slot, membership,       
                          equipment maintenance   
  -----------------------------------------------------------------------

### 4.10 Kewangan, insurans, korporat dan perkhidmatan pentadbiran

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Perniagaan SaaS         Plan, subscription,     B-Subscription
                          usage, billing,         Business + FIN + HELP
                          renewal, churn dan      
                          support                 

  Agensi insurans /       Lead, policy renewal    B-Professional
  takaful                 reminder dan document   Practice + DOCS; semak
                          workflow dalam skop     keperluan lesen
                          dibenarkan              

  Pemproses tuntutan      Claim intake, evidence  B-Insurance & Claims
                          checklist, status,      jika sesuai dan
                          assignment dan audit    dibenarkan

  Koperasi / persatuan    Ahli, yuran, mesyuarat, B-Community &
                          aktiviti, sumbangan dan Association + FIN
                          laporan                 

  NGO / badan kebajikan   Donor, donation         B-Community &
                          receipt, volunteer,     Association + PROJ +
                          program, grant dan      FIN
                          expenditure             

  Pengurusan pejabat      Asset, vendor, meeting  B-Virtual Office +
                          room, visitor dan       ASSET + BOOK
                          maintenance             

  Syarikat keselamatan    Guard roster, site      B-Security Services +
                          deployment, attendance, HR + HELP
                          incident report dan     
                          contract SLA            

  Pembersihan komersial   Site schedule, team,    B-Repair & Field
                          checklist, attendance,  Service + HR + INV
                          consumables dan         
                          contract billing        

  Perkhidmatan kurier     Pickup,                 LOG + DOCS
  dokumen                 chain-of-custody, proof 
                          of delivery dan invois  

  Pengurusan data / pusat Agent schedule, case,   HR + HELP + Analytics
  panggilan               call metrics dan        
                          quality review          

  Telekomunikasi / IT     Customer account,       INV + HELP +
  reseller                service ticket, device  B-Subscription Business
                          inventory, recurring    
                          billing                 
  -----------------------------------------------------------------------

### 4.11 Digital, media, komunikasi dan kandungan

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Web/app developer       Project,                PROJ + HELP + BILL
                          repository/reference    
                          links, milestones,      
                          timesheet, support SLA  
                          dan invoice             

  Hosting / domain        Customer,               B-Subscription
  reseller                domain/renewal, plan,   Business + HELP +
                          usage, ticket dan       B-Connect
                          recurring billing       

  Content creator /       Campaign contract,      B-Link + PROJ + MKT +
  influencer              deliverable, schedule,  BILL
                          link tracking dan       
                          invoice                 

  Studio rakaman /        Studio slot, equipment, BOOK + ASSET + BILL
  podcast                 crew, file delivery dan 
                          deposit                 

  Media / penerbitan      Editorial calendar,     PROJ + DOCS + B-Site
                          assignment, rights,     
                          production dan billing  

  E-learning / digital    Course, access          B-Childcare &
  content                 entitlement,            Education +
                          subscription, progress  B-Subscription Business
                          dan certificate         

  Affiliate publisher     Tracking link,          B-Affiliate + Analytics
                          campaign, conversion,   
                          payout dan fraud review 

  Penjual perkhidmatan    Package, onboarding,    Marketplace +
  digital                 deliverable,            Projects + BILL + HELP
                          subscription dan        
                          support                 
  -----------------------------------------------------------------------

### 4.12 Perkhidmatan komuniti, rumah tangga dan niche

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Pengasuh haiwan / pet   Pet profile, boarding   B-Pet Services + BOOK
  boarding                dates, feeding          
                          instructions, booking   
                          dan owner updates       

  Grooming haiwan         Appointment, service    B-Pet Services + POS
                          package, pet notes dan  
                          sales                   

  Kedai haiwan            Product, batch/expiry,  POS + INV + LOYALTY
                          inventory, loyalty dan  
                          supplier                

  Kedai bunga / florist   Pre-order, seasonal     POS + INV + LOG + BOOK
                          stock, delivery slot    
                          dan event order         

  Kedai hadiah / hamper   Custom order,           POS + INV + LOG
                          packaging, stock,       
                          delivery dan deposit    

  Kedai jahit / langsir   Measurement, material,  PROJ + INV + BOOK
                          work order, fitting dan 
                          installation            

  Penyewaan pakaian /     Size, fitting,          B-Event & Rental +
  kostum                  availability, deposit,  ASSET
                          damage dan return       

  Perkhidmatan pindah /   Inventory barang,       LOG + ASSET/Inventory +
  simpan barang           storage location, move  BILL
                          schedule, crew dan      
                          billing                 

  Servis fotografi        Queue, job, file, print POS + PROJ
  pasport / cetakan       order dan payment       

  Pengurusan jenazah /    Case workflow,          B-Professional
  perkhidmatan            schedule,               Practice + BOOK + LOG
  pengebumian             vehicle/resource,       
                          family contact dan      
                          invoice; kawalan        
                          privasi tinggi          

  Katering komuniti /     Event, booking, menu,   B-Event & Rental +
  dewan komuniti          volunteer, inventory    INV + FIN
                          dan invois              

  Perkhidmatan kunci /    Service call,           B-Repair & Field
  keselamatan rumah       technician, inventory   Service + INV + BOOK
                          dan work completion     
  -----------------------------------------------------------------------

### 4.13 Sumber asli, utiliti dan industri terkawal

  -----------------------------------------------------------------------
  Jenis perniagaan        Fungsi khusus           Modul BIJAN
  ----------------------- ----------------------- -----------------------
  Perlombongan / kuari    Site, equipment,        Modul industri khusus +
                          production volume,      ASSET + INV + LOG
                          maintenance, safety dan 
                          shipment                

  Minyak / gas / tenaga   Asset integrity,        Modul khusus + ASSET +
                          permit-to-work          Quality & Compliance
                          workflow, maintenance,  
                          contractor dan          
                          compliance evidence     

  Tenaga solar /          Survey, quotation,      PROJ + ASSET + INV +
  pemasangan              installation project,   BOOK
                          equipment serials,      
                          maintenance contract    

  Air / utiliti /         Work orders,            B-Repair & Field
  kontraktor utiliti      asset/network, field    Service + ASSET + BILL
                          crew, outage dan        
                          customer billing        

  Pengurusan alam sekitar Inspection, sampling    Quality & Compliance +
                          record, service         PROJ + DOCS
                          contract, report dan    
                          compliance workflow     

  Makmal ujian industri   Sample,                 Modul khusus + Quality
                          chain-of-custody,       & Compliance
                          result, quality system  
                          dan report              

  Aktiviti kewangan       Customer workflow dan   Perlu semakan lesen,
  terkawal                dokumen yang dibenarkan keselamatan dan skop
                          sahaja                  undang-undang
  -----------------------------------------------------------------------

### 4.14 Kerajaan, pendidikan awam dan organisasi bukan komersial

AI BIJAN boleh menyediakan workflow vendor atau organisasi jika sesuai,
tetapi tidak boleh menganggap sistem ini menggantikan sistem kerajaan
atau sistem rasmi. - Pembekal kerajaan: quotation, tender tracking,
purchase orders dan invoicing mengikut kontrak. - Persatuan penduduk:
ahli, aduan, mesyuarat, kemudahan dan kutipan yang dibenarkan. - Badan
profesional: ahli, renewal, training, event dan dokumentasi. -
Organisasi komuniti: volunteer, program, donation dan pelaporan. -
Institusi awam: hanya melalui skop, procurement, security review dan
integrasi yang diluluskan.

------------------------------------------------------------------------

## 5. Keutamaan khusus: B-School Transport untuk van sekolah

Ini ialah modul yang perlu dinamakan secara jelas, bukan sekadar diserap
dalam B-Logistics.

### 5.1 Jenis pengguna

-   Pengusaha/pemilik van atau bas sekolah.
-   Admin operasi/dispatcher.
-   Pemandu.
-   Pembantu pemandu jika ada.
-   Ibu bapa/penjaga.
-   Sekolah atau institusi yang mempunyai kontrak.
-   Admin platform AI BIJAN.

### 5.2 Fungsi MVP

1.  Profil murid dan penjaga.
2.  Sekolah, kelas atau sesi persekolahan.
3.  Pickup point, drop-off point, alamat dan arahan akses yang
    diperlukan.
4.  Laluan, turutan hentian dan jadual.
5.  Assign murid kepada laluan, kenderaan dan pemandu.
6.  Kapasiti kenderaan dan amaran kapasiti berlebihan.
7.  Jadual berulang mengikut hari sekolah.
8.  Yuran mingguan/bulanan, invois, tunggakan dan resit.
9.  Status pickup/drop-off dan pengesahan serahan.
10. Notifikasi kelewatan, perubahan jadual dan pembatalan.
11. Kontak kecemasan dan individu yang dibenarkan mengambil murid.
12. Rekod dokumen kenderaan/pemandu dan tarikh luput.
13. Jadual servis, pemeriksaan dan kos kenderaan.
14. Laporan operasi, pembayaran, kehadiran dan insiden.
15. Audit perubahan pada rekod murid, laluan dan individu yang
    dibenarkan.

### 5.3 Fasa lanjutan

-   Route optimization dengan had masa dan kapasiti.
-   ETA berdasarkan data lokasi yang sah dan tersedia.
-   Portal penjaga dengan status perjalanan.
-   Pelbagai sekolah, cawangan dan operator.
-   Kontrak institusi dan invois berkumpulan.
-   Offline-safe driver app dengan sync conflict handling.
-   Laporan kos per laluan dan keuntungan setiap kenderaan.

### 5.4 Keselamatan dan privasi

-   Akses penjaga hanya kepada anak/rekod yang dipautkan secara sah.
-   Pemandu hanya melihat maklumat yang diperlukan untuk tugasan semasa.
-   Elakkan memaparkan alamat rumah atau nombor telefon kepada pihak
    tidak berkaitan.
-   Rekod consent, retention dan akses.
-   Lokasi langsung bukan ciri wajib; aktifkan hanya apabila perlu,
    dimaklumkan dan dilindungi.
-   Sediakan prosedur insiden dan rekod audit.
-   Sahkan keperluan permit, lesen, pemeriksaan dan peraturan
    pengangkutan sekolah dengan pihak berkuasa berkaitan sebelum
    menawarkan fungsi pematuhan sebagai "disahkan".

------------------------------------------------------------------------

## 6. Industry Blueprint Engine

### 6.1 Aliran onboarding

1.  Pengguna mencipta organisasi.
2.  Pilih negara dan lokasi operasi.
3.  Pilih industri utama dan aktiviti sekunder.
4.  Pilih subjenis/model operasi.
5.  Jawab soalan konfigurasi ringkas.
6.  Sistem mencadangkan modul, role, workflow, borang, dashboard dan
    laporan.
7.  Pengguna menyemak serta mengesahkan pilihan.
8.  Sistem menyediakan tenant tanpa mengaktifkan integrasi berbayar
    tanpa kebenaran.
9.  Pengguna melengkapkan setup data dan latihan.
10. Sistem menjalankan checklist readiness.

### 6.2 Contoh soalan mengikut jenis perniagaan

**Van sekolah** - Berapa kenderaan dan pemandu? - Berapa
sekolah/laluan? - Kutipan bulanan atau mengikut perjalanan? - Perlu
portal penjaga? - Perlu pengesahan pickup/drop-off? - Adakah kontrak
dengan sekolah?

**Dobi** - Walk-in, pickup/delivery atau kedua-duanya? - Berat, item
atau pakej sebagai unit harga? - Berapa outlet dan mesin? - Perlu
tracking status cucian?

**Kontraktor** - Jenis projek? - Perlu BOQ, progress claim dan VO? -
Berapa projek dan subkontraktor? - Perlu rekod site dan material?

### 6.3 Skema blueprint

Setiap blueprint hendaklah menyimpan: - `industry_id` -
`subindustry_id` - `msic_code` (nullable sehingga disahkan) -
`msic_version` - `recommended_modules` - `required_modules` -
`optional_modules` - `default_roles` - `workflow_templates` -
`custom_fields` - `terminology_map` - `dashboard_widgets` -
`report_templates` - `integration_recommendations` -
`localization_profile` - `permission_profile` -
`acceptance_test_suite` - `version`, `status`, `reviewed_at`

### 6.4 Pengurusan katalog

-   Simpan katalog industri sebagai data/config yang boleh dikemas kini,
    bukan `if/else` yang tersebar dalam kod.
-   Sokong industri, subindustri dan aktiviti sekunder.
-   Simpan alias carian: "van sekolah", "school van", "pengangkutan
    murid".
-   Bezakan kategori pemasaran daripada kod MSIC rasmi.
-   Kemas kini versi MSIC secara terkawal dan jangan menukar kod sejarah
    secara senyap.
-   Benarkan admin mengemukakan subjenis baharu untuk disemak.

------------------------------------------------------------------------

## 7. Cara memutuskan: modul baharu atau konfigurasi?

Bina **modul khusus** apabila perniagaan mempunyai: - Entiti data yang
unik. - Aliran kerja kritikal yang tidak sesuai dengan modul generik. -
Keperluan keselamatan atau compliance yang berbeza. - Laporan operasi
yang khusus. - Pengguna atau peranan yang berbeza.

Gunakan **konfigurasi blueprint** apabila perbezaan hanya: - Nama
istilah. - Field tambahan yang tidak mengubah domain. - Status workflow
yang ringkas. - Dashboard/report layout. - Default role dan notification
template.

Contoh: - Van sekolah memerlukan B-School Transport khusus. - Dobi
mungkin boleh menggunakan order workflow generik yang diperluas, tetapi
tracking cucian, tag item dan status mesin mungkin memerlukan
submodul. - Barber menggunakan B-Booking dan POS dengan konfigurasi
industri. - Hospital memerlukan domain klinikal khusus, bukan hanya
menukar nama "customer" kepada "patient".

------------------------------------------------------------------------

## 8. Role dan kawalan akses

Role asas: - Platform Owner/Admin HQ - Business Owner - Organization
Admin - Branch Manager - Operations Manager - Finance Officer -
Staff/Operator - Driver/Field Worker - Customer/Parent/Guardian -
Supplier/Vendor - Affiliate/Partner - AI Agent (service identity)

Kawalan akses perlu merangkumi role, tenant, branch, department,
assigned record dan module entitlement. Role tidak boleh menjadi
satu-satunya kawalan.

Contoh van sekolah: - Pengusaha melihat data operasi perniagaannya. -
Pemandu melihat senarai perjalanan dan maklumat minimum yang
diperlukan. - Penjaga melihat anak/rekod yang dipautkan sahaja. -
Sekolah melihat hanya data yang dibenarkan melalui hubungan/kontrak. -
Admin platform tidak sepatutnya menyemak kandungan peribadi secara rutin
tanpa sebab dan permission audit.

------------------------------------------------------------------------

## 9. Shared Core dan sempadan rekod rasmi

  Data / transaksi             Sistem sumber
  ---------------------------- ----------------------------------------
  Identiti, membership, role   B-Auth & Security
  Langganan AI BIJAN           Platform Subscription & Billing
  Jualan kaunter               B-POS
  Pergerakan stok              B-Inventory
  Mata ganjaran                B-Loyalty
  Tempahan servis              B-Booking atau Industry Booking Module
  Rekod perakaunan             B-Finance
  Invois/quotation             B-Invoicing
  Pekerja/shift/cuti           B-HRMS
  Projek/milestone             B-Projects
  Aset fizikal                 B-Assets
  Penghantaran                 B-Logistics
  Data khusus van sekolah      B-School Transport
  Rekod klinikal               B-Clinic & Care, jika dibangunkan
  Penggunaan model AI          B-Brain Hub usage ledger

Semua integrasi mesti mempunyai ID rujukan, idempotency, event log,
retry, error handling dan reconciliation. Elakkan modul mengubah rekod
sumber secara terus tanpa API/domain service yang ditetapkan.

------------------------------------------------------------------------

## 10. B-Brain Hub untuk semua industri

B-Brain Hub boleh membantu pelbagai bisnes, tetapi agent perlu diberi
konteks dan tools yang relevan.

Contoh agent: - **Van School Operations Agent:** ringkaskan tunggakan,
amaran dokumen luput, cadangan penjadualan dan notifikasi draf. -
**Retail Analyst Agent:** analisis jualan, stok perlahan dan margin. -
**Cafe Cost Agent:** analisis kos resipi, pembaziran dan trend jualan. -
**Booking Assistant:** jawab soalan slot berdasarkan availability
sebenar. - **Project Assistant:** ringkaskan milestone dan risiko
kelewatan. - **Finance Assistant:** jelaskan laporan dan tandakan
transaksi untuk semakan; tidak meluluskan pembayaran sendiri. -
**Customer Support Agent:** cadangkan jawapan berdasarkan knowledge base
yang dibenarkan. - **Compliance Checklist Agent:** susun dokumen dan
senarai semak; tidak menjamin pematuhan undang-undang.

Setiap agent mesti mempunyai: - Tujuan dan skop. - Model yang dibenarkan
Admin HQ. - Permission dan tools minimum. - Memori khusus/berskop
tenant. - Token budget, rate limit dan timeout. - Audit penggunaan dan
tindakan. - Human approval untuk operasi berisiko.

API key model mesti disimpan di server. Pengguna membeli kredit/token
dalaman dan memilih model daripada katalog yang dibenarkan. Rekod
penggunaan dan harga hendaklah telus.

------------------------------------------------------------------------

## 11. Pakej industri contoh

### Pakej Van Sekolah

-   B-School Transport
-   B-Invoicing
-   B-Finance asas
-   B-Transport & Fleet
-   B-Omnichannel
-   B-Analytics
-   B-Brain Hub sebagai add-on

### Pakej Dobi

-   Order/workflow dobi
-   POS
-   Inventory bahan
-   Pickup/delivery melalui Logistics
-   Loyalty
-   Invoicing/Finance

### Pakej Taska

-   B-Childcare & Education
-   Invoicing
-   HRMS
-   Omnichannel
-   Booking/jadual
-   Security and guardian permissions

### Pakej Kontraktor

-   B-Construction
-   Projects
-   Procurement
-   Inventory
-   Invoicing/Finance
-   Sign & Docs
-   Assets

### Pakej Bengkel

-   B-Automotive
-   POS
-   Inventory alat ganti
-   Booking
-   Invoicing/Finance
-   Customer reminders

### Pakej Pengusaha Katering

-   B-Event & Rental
-   POS/Orders
-   COGS/BOM
-   Inventory
-   Booking
-   Logistics
-   Invoicing/Finance

Pakej ini ialah cadangan konfigurasi. Modul dan harga sebenar perlu
ditentukan berdasarkan skop MVP dan kos operasi.

------------------------------------------------------------------------

## 12. Keutamaan pembangunan

### Fasa 0 --- Discovery dan katalog

-   Pilih 3--5 jenis perniagaan pertama berdasarkan permintaan
    pelanggan.
-   Sediakan direktori industri dan alias carian.
-   Semak kod MSIC melalui sumber rasmi.
-   Dokumentasikan workflow, data dan acceptance tests.
-   Tentukan modul generik yang boleh dikongsi.

### Fasa 1 --- Shared Core

-   Tenant, auth, role, permissions dan audit.
-   Module entitlement, subscription dan usage metering.
-   Notification, file storage dan API foundation.
-   Ujian cross-tenant access.

### Fasa 2 --- Satu aliran perniagaan lengkap

Pilih satu industri utama dan selesaikan aliran hujung-ke-hujung sebelum
membina banyak modul separuh siap. Contohnya: - Van sekolah: daftar
murid → tetapkan laluan → tugaskan pemandu → rekod perjalanan → invois
bulanan → bayaran → laporan. - Retail: produk → jualan → stok →
invois/rekod kewangan → laporan. - Salon: service → slot → booking →
bayaran → loyalty → laporan.

### Fasa 3 --- Tambah industri berdekatan

Gunakan modul sedia ada apabila domain sesuai. Tambah modul khusus
apabila proses kerja benar-benar berlainan.

### Fasa 4 --- Industry Blueprint Engine

-   Onboarding industri.
-   Modul yang dicadangkan.
-   Custom fields dan workflow templates.
-   Dashboard dan report presets.
-   Versioning dan admin review.

### Fasa 5 --- AI agents dan automasi

-   AI gateway, model catalog dan token wallet.
-   Agent builder, memory boundaries dan tools.
-   Approval gates, budget limits dan audit.
-   Ujian kebocoran data, ketepatan dan kos.

### Fasa 6 --- Skala dan pengembangan

-   Multi-branch dan enterprise workflows.
-   Integrasi pihak ketiga.
-   Industry acceptance suites.
-   Localization negara lain selepas Malaysia stabil.

------------------------------------------------------------------------

## 13. Definition of Done bagi setiap industri

Sektor tidak boleh ditandakan "disokong" hanya kerana ia muncul dalam
dropdown.

Setiap industry blueprint mesti mempunyai: 1. Persona dan peranan. 2.
Workflow utama hujung-ke-hujung. 3. Senarai entiti/data. 4. Modul yang
diperlukan dan pilihan. 5. Permission matrix. 6. Dokumen dan notifikasi.
7. KPI dan laporan. 8. Integration contracts. 9. Ujian role dan tenant
isolation. 10. Ujian aliran kerja dan error recovery. 11. Dokumentasi
setup dan pengguna. 12. Semakan keperluan undang-undang/lesen yang
relevan. 13. Status sokongan yang jelas.

### Status sokongan yang dicadangkan

-   **Planned:** dalam roadmap, belum dibangunkan.
-   **Blueprint Ready:** workflow dan skop didokumenkan.
-   **MVP:** fungsi teras dibangunkan, masih dengan had yang dinyatakan.
-   **Production Ready:** ujian, keselamatan, monitoring, backup dan
    dokumentasi lengkap.
-   **Regulated Workflow Reviewed:** proses dan keperluan terkawal telah
    disemak oleh pihak yang layak; bukan jaminan mutlak pematuhan.

------------------------------------------------------------------------

## 14. Had dan perkara yang memerlukan semakan khusus

-   MSIC ialah taksonomi aktiviti ekonomi, bukan spesifikasi produk
    perisian.
-   Senarai sektor dalam dokumen ini ialah direktori produk yang luas,
    bukan pemetaan lengkap setiap kod MSIC.
-   Kod MSIC tepat perlu dicari dan disahkan di portal DOSM mengikut
    aktiviti sebenar.
-   Pengangkutan sekolah, penjagaan kanak-kanak, klinik, farmasi,
    kewangan, insurans, undang-undang, keselamatan dan industri terkawal
    memerlukan semakan keperluan lesen, permit, perlindungan data serta
    peraturan semasa.
-   Integrasi GPS, peta, pembayaran, WhatsApp, e-Invoice dan pihak
    ketiga memerlukan konfigurasi, kebenaran, kos dan pengujian.
-   AI tidak boleh menggantikan keputusan profesional atau pihak
    berkuasa bagi perkara yang memerlukan kelayakan/kelulusan.
-   Jangan simpan data kanak-kanak, kesihatan, kewangan atau lokasi
    lebih daripada yang diperlukan.
-   Jangan menjanjikan tracking masa nyata jika tiada peranti, consent,
    sambungan dan sumber lokasi yang sah.

------------------------------------------------------------------------

## 15. Sumber rasmi untuk penyelenggaraan katalog

1.  **DOSM --- Kod MSIC 2025:**
    https://economiccensus.dosm.gov.my/portal-main/msic-2025
2.  **OpenDOSM --- MSIC data catalogue:**
    https://open.dosm.gov.my/data-catalogue/msic
3.  **DOSM --- Economic Census, All Sectors:**
    https://www.dosm.gov.my/portal-main/release-content/economic-census--all-sectors
4.  **DOSM --- MSME Performance:**
    https://www.dosm.gov.my/portal-main/release-content/micro-small--medium-enterprises-msmes-performance-2025
5.  **SME Corp Malaysia --- definisi PKS:**
    https://smecorp.gov.my/index.php/my/component/content/article/87-definisi-pks-sme-info/371-sme-definition

Semak tarikh, versi dan kandungan terkini sebelum menggunakan
klasifikasi atau angka dalam keputusan rasmi.

------------------------------------------------------------------------

## 16. Keputusan akhir

AI BIJAN perlu dibangunkan sebagai **platform SaaS modular berasaskan
direktori industri Malaysia**. Direktori mesti meliputi perniagaan umum
dan khusus --- termasuk pengusaha van sekolah, bas sekolah, taska, dobi,
kontraktor, bengkel, petani, katering, pengusaha acara, penjagaan haiwan
dan banyak lagi.

Tidak semua jenis perniagaan memerlukan aplikasi berasingan. Sebahagian
menggunakan gabungan modul generik, manakala industri dengan operasi
unik memerlukan Industry Module tersendiri. Industry Blueprint Engine
menggabungkan kedua-duanya, mengesyorkan konfigurasi yang sesuai dan
memastikan setiap jenis perniagaan mempunyai workflow serta ujian
penerimaan.

**Prinsip produk:**\
*One platform. Many specialized business systems. Configured for
Malaysia's industries. Powered by AI.*

**Tindakan seterusnya:** gunakan dokumen ini sebagai blueprint katalog
industri; pilih industri MVP berdasarkan permintaan pelanggan, sahkan
kod MSIC rasmi, kemudian bangunkan satu aliran kerja hujung-ke-hujung
sebelum menambah industri seterusnya.
