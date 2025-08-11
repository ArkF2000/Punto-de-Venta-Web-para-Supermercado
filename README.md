# Punto de Venta Web para Supermercado

## Descripción del Proyecto
Este proyecto es un **sistema web de Punto de Venta (POS)** diseñado para la gestión integral de un supermercado.  
Permite administrar ventas, inventario, facturación, ingresos y egresos de productos, con control de acceso por roles (administrador y cajero).  
Las facturas se generan en formato PDF con numeración correlativa y cálculo automático de impuestos mediante una API.

---

## Características Principales
### 1. Autenticación de Usuarios
- Login seguro con roles:
  - **Administrador**: Gestión de inventario, usuarios, reportes y configuración.
  - **Cajero**: Acceso a ventas y emisión de facturas.

### 2. Gestión de Inventario
- Alta, edición, eliminación y visualización de productos.
- Registro de entradas (ingresos) y salidas (egresos) de productos.
- Campos por producto: ID, nombre, descripción, categoría, precio de venta, precio de compra y stock.

### 3. Ventas y Facturación
- Registro de ventas en tiempo real.
- Generación automática de facturas en PDF con:
  - Número correlativo único.
  - Fecha y hora.
  - Detalle de productos vendidos.
  - Subtotal, impuestos y total.
- Actualización automática de inventario.
- Registro de transacciones en historial.

### 4. Cálculo de Impuestos
- Integración con una API pública o externa para cálculo de impuestos (ej. IVA).

### 5. Base de Datos
- Base de datos relacional (MySQL/MariaDB).
- Incluye script SQL para creación de tablas:
  - `usuarios`
  - `roles`
  - `productos`
  - `ventas`
  - `facturas`
  - `detalle_factura`
  - `historial`
  - entre otras necesarias.

---

## Tecnologías Utilizadas
- **Backend:** PHP, Python (Flask/Django) o Node.js
- **Frontend:** HTML, CSS, JavaScript
- **Base de Datos:** MySQL o MariaDB
- **Generación de PDFs:** FPDF, TCPDF o equivalente
- **API de Impuestos:** API pública o simulada

---

## Flujo General del Sistema
1. Usuario inicia sesión según su rol.
2. Administrador gestiona inventario, usuarios y reportes.
3. Cajero registra ventas seleccionando productos.
4. El sistema calcula subtotal e impuestos (vía API).
5. Se genera factura en PDF con número correlativo.
6. Se actualiza el inventario automáticamente.
7. La venta queda registrada para reportes posteriores.

