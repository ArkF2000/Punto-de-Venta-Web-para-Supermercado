# 🛒 **Punto de Venta Web para Supermercado**

## 📌 **Descripción del Proyecto**
Este proyecto es un **Sistema Web de Punto de Venta (POS)** diseñado para la **gestión integral de un supermercado**.  
Permite **administrar ventas, inventario, facturación, ingresos y egresos** de productos, con control de acceso por **roles** (Administrador y Cajero).  
Las **facturas** se generan en formato **PDF** con numeración correlativa y cálculo automático de impuestos mediante una **API**.

---

## ✨ **Características Principales**

### 1️⃣ Autenticación de Usuarios
- 🔐 **Login seguro** con roles:
  - 🛠 **Administrador** → Gestión de inventario, usuarios, reportes y configuración.
  - 💰 **Cajero** → Acceso a ventas y emisión de facturas.

### 2️⃣ Gestión de Inventario
- 📦 Creacion, edición, eliminación y visualización de productos.
- ➕ Registro de **ingresos** y ➖ **egresos** de productos.
- 🏷 Campos por producto:  
  `ID`, `Nombre`, `Descripción`, `Categoría`, `Precio de Venta`, `Precio de Compra`, `Stock`.

### 3️⃣ Ventas y Facturación
- ⚡ Registro de ventas **en tiempo real**.
- 🧾 Generación automática de facturas en **PDF** con:
  - 🔢 Número correlativo único.
  - 📅 Fecha y hora.
  - 📋 Detalle de productos vendidos.
  - 💵 Subtotal, impuestos y total.
- 📉 Actualización automática de inventario.
- 📜 Registro de transacciones en historial.

### 4️⃣ Cálculo de Impuestos
- 🌐 Integración con una **API pública** externa para cálculo de impuestos (IVA).

### 5️⃣ Base de Datos
- 🗄 **Base de datos relacional** (MySQL/MariaDB).
- 📂 Incluye script SQL para tablas:
  - `usuarios`
  - `roles`
  - `productos`
  - `ventas`
  - `facturas`
  - `detalle_factura`
  - `historial`
  - *(y otras necesarias)*

---

## 🛠 **Tecnologías Utilizadas**
- **Backend:** PHP, JavaScript 
- **Frontend:** HTML, CSS, JavaScript  
- **Base de Datos:** MySQL
- **Generación de PDFs:** FPDF, TCPDF 
- **API de Impuestos:** Se usó una API pública simulada  

---

## 🔄 **Flujo General del Sistema**
1. 👤 Usuario inicia sesión según su rol.
2. 🛠 Administrador gestiona inventario, usuarios y reportes.
3. 💰 Cajero registra ventas seleccionando productos.
4. 🧮 El sistema calcula subtotal e impuestos (vía API).
5. 🧾 Se genera factura en PDF con número correlativo.
6. 📉 Se actualiza el inventario automáticamente.
7. 📊 La venta queda registrada para reportes posteriores.

---

> 💡 **Nota:** Este sistema está diseñado para ser escalable y adaptable a distintos tamaños de negocio, permitiendo su implementación tanto en supermercados como en otros comercios.



